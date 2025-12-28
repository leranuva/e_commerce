<?php

namespace App\Http\Controllers;

use App\Domains\Sales\Actions\CreateOrderAction;
use App\Domains\Sales\Actions\ChangeOrderStatusAction;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

/**
 * Controlador para manejar webhooks de Stripe.
 * 
 * CRÍTICO: Este controlador valida las firmas de Stripe para asegurar
 * que los eventos son auténticos. NUNCA confíes en eventos que no
 * vengan de Stripe directamente.
 */
class StripeWebhookController extends Controller
{
    protected PaymentService $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Manejar webhook de Stripe.
     * 
     * Esta ruta debe estar excluida de CSRF en VerifyCsrfToken middleware.
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');
        
        if (!$webhookSecret) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }
        
        try {
            // Validar la firma del webhook
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Payload inválido
            Log::error('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Firma inválida - posible ataque
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        
        // Procesar el evento
        return $this->processEvent($event);
    }
    
    /**
     * Procesar evento de Stripe.
     */
    protected function processEvent($event)
    {
        $paymentIntent = $event->data->object;
        $paymentIntentId = $paymentIntent->id;
        
        Log::info('Stripe webhook received', [
            'type' => $event->type,
            'payment_intent_id' => $paymentIntentId,
        ]);
        
        switch ($event->type) {
            case 'payment_intent.succeeded':
                return $this->handlePaymentSucceeded($paymentIntent);
                
            case 'payment_intent.payment_failed':
                return $this->handlePaymentFailed($paymentIntent);
                
            case 'payment_intent.canceled':
                return $this->handlePaymentCanceled($paymentIntent);
                
            default:
                Log::info('Unhandled Stripe event type', ['type' => $event->type]);
                return response()->json(['received' => true]);
        }
    }
    
    /**
     * Manejar pago exitoso.
     * 
     * CRÍTICO: Solo aquí se crea/confirma la orden.
     * El cliente NUNCA puede crear órdenes directamente.
     */
    protected function handlePaymentSucceeded($paymentIntent)
    {
        $paymentIntentId = $paymentIntent->id;
        
        // Buscar orden pendiente por payment_intent_id
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        
        if ($order) {
            // Orden ya existe (creada como pendiente), solo cambiar estado
            try {
                ChangeOrderStatusAction::run($order, OrderStatus::PAID);
                
                Log::info('Order confirmed via webhook', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
                
                // Aquí podrías disparar eventos, enviar emails, etc.
                // event(new OrderPaid($order));
                
                return response()->json(['status' => 'order_confirmed'], 200);
            } catch (\Exception $e) {
                Log::error('Failed to confirm order via webhook', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
                
                return response()->json(['error' => 'Failed to confirm order'], 500);
            }
        }
        
        // Si no existe orden, verificar metadata para crear una nueva
        // (esto es para casos donde el webhook llega antes que se guarde la orden pendiente)
        $metadata = $paymentIntent->metadata ?? [];
        
        if (isset($metadata['order_data'])) {
            // Reconstruir datos de la orden desde metadata
            // Esto es un fallback, idealmente la orden debería existir ya
            Log::warning('Order not found, attempting to create from metadata', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            
            // Por seguridad, no creamos órdenes desde metadata sin validación adicional
            // En producción, esto debería requerir validación adicional
        }
        
        Log::warning('Payment succeeded but no order found', [
            'payment_intent_id' => $paymentIntentId,
        ]);
        
        return response()->json(['status' => 'payment_succeeded_no_order'], 200);
    }
    
    /**
     * Manejar pago fallido.
     */
    protected function handlePaymentFailed($paymentIntent)
    {
        $paymentIntentId = $paymentIntent->id;
        
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        
        if ($order) {
            // Cancelar la orden
            try {
                if ($order->canTransitionTo(OrderStatus::CANCELLED)) {
                    ChangeOrderStatusAction::run($order, OrderStatus::CANCELLED);
                }
                
                Log::info('Order cancelled due to failed payment', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to cancel order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return response()->json(['status' => 'payment_failed_handled'], 200);
    }
    
    /**
     * Manejar pago cancelado.
     */
    protected function handlePaymentCanceled($paymentIntent)
    {
        $paymentIntentId = $paymentIntent->id;
        
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        
        if ($order) {
            try {
                if ($order->canTransitionTo(OrderStatus::CANCELLED)) {
                    ChangeOrderStatusAction::run($order, OrderStatus::CANCELLED);
                }
                
                Log::info('Order cancelled via webhook', [
                    'order_id' => $order->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to cancel order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return response()->json(['status' => 'payment_canceled_handled'], 200);
    }
}


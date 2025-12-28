<?php

namespace App\Services;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * Service para manejar pagos con Stripe.
 * 
 * Encapsula toda la lógica de integración con Stripe,
 * incluyendo creación de PaymentIntents y manejo de webhooks.
 */
class PaymentService
{
    protected StripeClient $stripe;
    
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }
    
    /**
     * Crear un PaymentIntent para una orden.
     * 
     * @param float $amount Monto en la moneda base (ej: 100.00 para $100.00)
     * @param string $currency Código de moneda (default: 'usd')
     * @param array $metadata Metadata adicional (ej: order_id, customer_id)
     * @return array ['client_secret' => string, 'payment_intent_id' => string]
     * @throws \Exception
     */
    public function createPaymentIntent(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            // Stripe usa centavos, convertir a centavos
            $amountInCents = (int) ($amount * 100);
            
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);
            
            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ];
        } catch (ApiErrorException $e) {
            logger()->error('Stripe PaymentIntent creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            
            throw new \Exception('Error al procesar el pago: ' . $e->getMessage());
        }
    }
    
    /**
     * Confirmar un PaymentIntent.
     * 
     * @param string $paymentIntentId
     * @return bool
     */
    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            
            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            logger()->error('Stripe PaymentIntent confirmation failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);
            
            return false;
        }
    }
    
    /**
     * Obtener el estado de un PaymentIntent.
     * 
     * @param string $paymentIntentId
     * @return string|null Estado del PaymentIntent o null si hay error
     */
    public function getPaymentStatus(string $paymentIntentId): ?string
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->status;
        } catch (ApiErrorException $e) {
            logger()->error('Stripe PaymentIntent retrieval failed', [
                'error' => $e->getMessage(),
                'payment_intent_id' => $paymentIntentId,
            ]);
            
            return null;
        }
    }
    
    /**
     * Procesar webhook de Stripe.
     * 
     * @param array $payload Payload del webhook
     * @return array Información procesada
     */
    public function handleWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        $paymentIntent = $payload['data']['object'] ?? null;
        
        if (!$eventType || !$paymentIntent) {
            return ['processed' => false, 'reason' => 'Invalid payload'];
        }
        
        // Procesar diferentes tipos de eventos
        switch ($eventType) {
            case 'payment_intent.succeeded':
                return [
                    'processed' => true,
                    'payment_intent_id' => $paymentIntent['id'],
                    'status' => 'succeeded',
                    'amount' => $paymentIntent['amount'] / 100, // Convertir de centavos
                ];
                
            case 'payment_intent.payment_failed':
                return [
                    'processed' => true,
                    'payment_intent_id' => $paymentIntent['id'],
                    'status' => 'failed',
                    'error' => $paymentIntent['last_payment_error']['message'] ?? 'Payment failed',
                ];
                
            default:
                return ['processed' => false, 'reason' => 'Event type not handled'];
        }
    }
}


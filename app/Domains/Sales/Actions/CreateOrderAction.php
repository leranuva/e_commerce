<?php

namespace App\Domains\Sales\Actions;

use App\Actions\Action;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use App\Domains\Customers\Models\Customer;
use Illuminate\Support\Facades\DB;

/**
 * Action para crear una nueva orden.
 * 
 * Encapsula toda la lógica de negocio relacionada con la creación de órdenes,
 * incluyendo validaciones, cálculos y transacciones.
 */
class CreateOrderAction extends Action
{
    /**
     * Crea una nueva orden a partir de los datos del carrito.
     * 
     * @param Customer $customer
     * @param array $cartItems Array con los items del carrito
     * @param array $shippingData Datos de envío
     * @param string|null $paymentIntentId ID del PaymentIntent de Stripe (opcional)
     * @return Order
     * @throws \Exception
     */
    public function execute(Customer $customer, array $cartItems, array $shippingData, ?string $paymentIntentId = null): Order
    {
        return DB::transaction(function () use ($customer, $cartItems, $shippingData, $paymentIntentId) {
            // Calcular totales
            $subtotal = $this->calculateSubtotal($cartItems);
            $tax = $this->calculateTax($subtotal);
            $shipping = $this->calculateShipping($shippingData);
            $total = $subtotal + $tax + $shipping;

            // Crear la orden con estado inicial usando State Machine
            // IMPORTANTE: La orden se crea como PENDING y solo se confirma cuando
            // el webhook de Stripe confirma el pago exitoso
            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'total' => $total,
                'status' => OrderStatus::PENDING, // Estado inicial - se confirma con webhook
                'payment_intent_id' => $paymentIntentId, // Vinculado al PaymentIntent
                'shipping_address' => $shippingData['address'] ?? null,
                'shipping_city' => $shippingData['city'] ?? null,
                'shipping_postal_code' => $shippingData['postal_code'] ?? null,
            ]);

            // Crear los items de la orden
            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            // Aquí podrías disparar eventos, enviar notificaciones, etc.
            // event(new OrderCreated($order));

            return $order->load('items');
        });
    }

    /**
     * Calcula el subtotal de los items del carrito.
     */
    private function calculateSubtotal(array $cartItems): float
    {
        return collect($cartItems)->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });
    }

    /**
     * Calcula el impuesto.
     */
    private function calculateTax(float $subtotal): float
    {
        // Ejemplo: 16% de IVA
        return $subtotal * 0.16;
    }

    /**
     * Calcula el costo de envío.
     */
    private function calculateShipping(array $shippingData): float
    {
        // Lógica para calcular envío basado en ubicación, peso, etc.
        return 50.00; // Ejemplo fijo
    }
}


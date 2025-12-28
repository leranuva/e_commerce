<?php

namespace App\Domains\Sales\Actions;

use App\Actions\Action;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use InvalidArgumentException;

/**
 * Action para cambiar el estado de una orden usando el State Machine.
 * 
 * Valida que la transición sea válida antes de cambiar el estado.
 */
class ChangeOrderStatusAction extends Action
{
    /**
     * Cambia el estado de una orden.
     * 
     * @param Order $order
     * @param string $newStatus
     * @return Order
     * @throws InvalidArgumentException
     */
    public function execute(Order $order, string $newStatus): Order
    {
        // Validar que el estado sea válido
        if (!in_array($newStatus, OrderStatus::all())) {
            throw new InvalidArgumentException("Estado inválido: {$newStatus}");
        }

        // Validar que la transición sea permitida
        if (!$order->canTransitionTo($newStatus)) {
            $allowed = OrderStatus::getTransitions($order->status);
            throw new InvalidArgumentException(
                "No se puede cambiar de '{$order->status}' a '{$newStatus}'. " .
                "Transiciones permitidas: " . implode(', ', $allowed)
            );
        }

        // Cambiar el estado
        $order->transitionTo($newStatus);

        // Aquí podrías disparar eventos, enviar notificaciones, etc.
        // event(new OrderStatusChanged($order, $oldStatus, $newStatus));

        return $order->fresh();
    }
}


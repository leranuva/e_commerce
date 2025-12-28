<?php

namespace App\Domains\Sales\States;

/**
 * State Machine para el estado de las órdenes.
 * 
 * Define los estados válidos y las transiciones permitidas:
 * Pendiente -> Pagado -> Enviado -> Entregado
 * 
 * También permite cancelación desde cualquier estado.
 */
class OrderStatus
{
    // Estados válidos
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    /**
     * Obtener todos los estados válidos.
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }

    /**
     * Obtener las transiciones permitidas desde un estado.
     */
    public static function getTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::PENDING => [self::PAID, self::CANCELLED],
            self::PAID => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED => [], // Estado final, no se puede cambiar
            self::CANCELLED => [], // Estado final, no se puede cambiar
            default => [],
        };
    }

    /**
     * Verificar si una transición es válida.
     */
    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::getTransitions($from));
    }

    /**
     * Obtener el label en español de un estado.
     */
    public static function getLabel(string $status): string
    {
        return match ($status) {
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagado',
            self::SHIPPED => 'Enviado',
            self::DELIVERED => 'Entregado',
            self::CANCELLED => 'Cancelado',
            default => $status,
        };
    }

    /**
     * Verificar si un estado es final (no puede cambiar).
     */
    public static function isFinal(string $status): bool
    {
        return in_array($status, [self::DELIVERED, self::CANCELLED]);
    }
}


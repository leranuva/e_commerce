<?php

namespace App\Domains\Sales\Models;

use App\Domains\Sales\States\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Customers\Models\Customer;
use InvalidArgumentException;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'status',
        'payment_intent_id',
        'shipping_address',
        'shipping_city',
        'shipping_postal_code',
    ];

    protected $attributes = [
        'status' => OrderStatus::PENDING,
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Relación con el cliente.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relación con los items de la orden.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Cambiar el estado de la orden usando el State Machine.
     * 
     * @param string $newStatus
     * @return bool
     * @throws InvalidArgumentException
     */
    public function transitionTo(string $newStatus): bool
    {
        if (!OrderStatus::canTransition($this->status, $newStatus)) {
            throw new InvalidArgumentException(
                "No se puede cambiar de '{$this->status}' a '{$newStatus}'. " .
                "Transiciones permitidas: " . implode(', ', OrderStatus::getTransitions($this->status))
            );
        }

        $this->status = $newStatus;
        return $this->save();
    }

    /**
     * Verificar si se puede cambiar a un estado.
     */
    public function canTransitionTo(string $status): bool
    {
        return OrderStatus::canTransition($this->status, $status);
    }

    /**
     * Obtener el label del estado actual.
     */
    public function getStatusLabelAttribute(): string
    {
        return OrderStatus::getLabel($this->status);
    }

    /**
     * Verificar si la orden está en un estado final.
     */
    public function isFinal(): bool
    {
        return OrderStatus::isFinal($this->status);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Domains\Sales\OrderFactory::new();
    }
}


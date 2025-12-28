<?php

namespace App\Domains\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attribute extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'type', // 'text', 'number', 'select', etc.
        'is_filterable',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
    ];

    /**
     * Relación con productos.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_attributes')
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\Domains\Catalog\AttributeFactory::new();
    }
}


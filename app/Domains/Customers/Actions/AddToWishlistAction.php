<?php

namespace App\Domains\Customers\Actions;

use App\Actions\Action;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\Wishlist;

/**
 * Action para agregar un producto a la wishlist del cliente.
 */
class AddToWishlistAction extends Action
{
    /**
     * Agrega un producto (o variante) a la wishlist.
     * 
     * @param Customer $customer
     * @param Product $product
     * @param ProductVariant|null $variant
     * @return Wishlist
     */
    public function execute(Customer $customer, Product $product, ?ProductVariant $variant = null): Wishlist
    {
        // Verificar si ya existe en la wishlist
        $existing = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Crear nuevo item en wishlist
        return Wishlist::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
        ]);
    }
}


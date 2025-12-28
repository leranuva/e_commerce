<?php

namespace App\Domains\Customers\Actions;

use App\Actions\Action;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Customers\Models\Customer;
use App\Domains\Customers\Models\Wishlist;

/**
 * Action para remover un producto de la wishlist.
 */
class RemoveFromWishlistAction extends Action
{
    /**
     * Remueve un producto (o variante) de la wishlist.
     * 
     * @param Customer $customer
     * @param Product $product
     * @param ProductVariant|null $variant
     * @return bool
     */
    public function execute(Customer $customer, Product $product, ?ProductVariant $variant = null): bool
    {
        $wishlistItem = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('variant_id', $variant?->id)
            ->first();

        if ($wishlistItem) {
            return $wishlistItem->delete();
        }

        return false;
    }
}


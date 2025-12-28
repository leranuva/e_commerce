<?php

namespace App\Livewire;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Customers\Actions\AddToWishlistAction;
use App\Domains\Customers\Actions\RemoveFromWishlistAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WishlistButton extends Component
{
    public Product $product;
    public ?ProductVariant $variant = null;
    public bool $isInWishlist = false;

    public function mount(Product $product, ?ProductVariant $variant = null)
    {
        $this->product = $product;
        $this->variant = $variant;
        $this->checkWishlistStatus();
    }

    /**
     * Toggle wishlist: agregar o remover según el estado actual.
     */
    public function toggle()
    {
        if (!Auth::guard('customer')->check()) {
            session()->flash('message', 'Debes iniciar sesión para agregar a tu lista de deseos');
            return;
        }

        $customer = Auth::guard('customer')->user();

        if ($this->isInWishlist) {
            RemoveFromWishlistAction::run($customer, $this->product, $this->variant);
            $this->isInWishlist = false;
            $this->dispatch('wishlist-updated');
            session()->flash('message', 'Producto removido de tu lista de deseos');
        } else {
            AddToWishlistAction::run($customer, $this->product, $this->variant);
            $this->isInWishlist = true;
            $this->dispatch('wishlist-updated');
            session()->flash('message', 'Producto agregado a tu lista de deseos');
        }
    }

    /**
     * Verificar si el producto está en la wishlist del usuario.
     */
    private function checkWishlistStatus(): void
    {
        if (!Auth::guard('customer')->check()) {
            $this->isInWishlist = false;
            return;
        }

        $customer = Auth::guard('customer')->user();
        
        $this->isInWishlist = \App\Domains\Customers\Models\Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $this->product->id)
            ->where('variant_id', $this->variant?->id)
            ->exists();
    }

    public function render()
    {
        return view('livewire.wishlist-button');
    }
}


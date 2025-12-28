<?php

namespace App\Livewire;

use App\Domains\Customers\Actions\RemoveFromWishlistAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class WishlistPage extends Component
{
    use WithPagination;

    public function removeFromWishlist($wishlistId)
    {
        if (!Auth::guard('customer')->check()) {
            return;
        }

        $customer = Auth::guard('customer')->user();
        $wishlistItem = \App\Domains\Customers\Models\Wishlist::findOrFail($wishlistId);

        if ($wishlistItem->customer_id !== $customer->id) {
            session()->flash('error', 'No tienes permiso para realizar esta acción');
            return;
        }

        RemoveFromWishlistAction::run(
            $customer,
            $wishlistItem->product,
            $wishlistItem->variant
        );

        session()->flash('message', 'Producto removido de tu lista de deseos');
    }

    public function render()
    {
        if (!Auth::guard('customer')->check()) {
            return view('livewire.wishlist-page', [
                'wishlistItems' => collect([]),
            ]);
        }

        $customer = Auth::guard('customer')->user();
        $wishlistItems = $customer->wishlist()
            ->with(['product', 'variant'])
            ->latest()
            ->paginate(12);

        return view('livewire.wishlist-page', [
            'wishlistItems' => $wishlistItems,
        ]);
    }
}


<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    protected CartService $cartService;
    
    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function getItemsProperty(): array
    {
        return $this->cartService->getItemsWithProducts();
    }
    
    public function getSubtotalProperty(): float
    {
        return $this->cartService->getSubtotal();
    }
    
    public function getTaxProperty(): float
    {
        // 16% IVA
        return $this->subtotal * 0.16;
    }
    
    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax;
    }
    
    /**
     * Actualizar cantidad de un item.
     */
    public function updateQuantity(string $itemId, int $quantity)
    {
        try {
            $this->cartService->update($itemId, $quantity);
            $this->dispatch('cart-updated');
            session()->flash('cart-message', 'Carrito actualizado');
        } catch (\Exception $e) {
            $this->addError('cart', $e->getMessage());
        }
    }
    
    /**
     * Remover item del carrito.
     */
    public function removeItem(string $itemId)
    {
        $this->cartService->remove($itemId);
        $this->dispatch('cart-updated');
        session()->flash('cart-message', 'Producto removido del carrito');
    }
    
    /**
     * Limpiar todo el carrito.
     */
    public function clearCart()
    {
        $this->cartService->clear();
        $this->dispatch('cart-updated');
        session()->flash('cart-message', 'Carrito vaciado');
    }
    
    protected $listeners = ['cart-updated' => '$refresh'];
    
    public function render()
    {
        return view('livewire.cart');
    }
}


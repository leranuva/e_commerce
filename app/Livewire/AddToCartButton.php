<?php

namespace App\Livewire;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Services\CartService;
use Livewire\Component;

class AddToCartButton extends Component
{
    public Product $product;
    public ?ProductVariant $variant = null;
    public int $quantity = 1;
    public bool $showQuantity = true;
    
    protected CartService $cartService;
    
    /**
     * Obtener el stock disponible (variante o producto).
     */
    public function getAvailableStockProperty(): int
    {
        return $this->variant?->stock ?? $this->product->stock;
    }
    
    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function mount(Product $product, ?ProductVariant $variant = null, bool $showQuantity = true)
    {
        $this->product = $product;
        $this->variant = $variant;
        $this->showQuantity = $showQuantity;
    }
    
    /**
     * Agregar producto al carrito.
     */
    public function addToCart()
    {
        try {
            // Validar stock
            $availableStock = $this->variant?->stock ?? $this->product->stock;
            
            if ($this->quantity > $availableStock) {
                $this->addError('quantity', "Solo hay {$availableStock} unidades disponibles");
                return;
            }
            
            if ($this->quantity <= 0) {
                $this->addError('quantity', 'La cantidad debe ser mayor a 0');
                return;
            }
            
            // Agregar al carrito
            $this->cartService->add($this->product, $this->quantity, $this->variant);
            
            // Disparar evento para actualizar contador del carrito
            $this->dispatch('cart-updated');
            
            // Mensaje de éxito
            session()->flash('message', 'Producto agregado al carrito');
            
            // Resetear cantidad si es necesario
            if ($this->showQuantity) {
                $this->quantity = 1;
            }
            
        } catch (\Exception $e) {
            $this->addError('cart', $e->getMessage());
        }
    }
    
    public function increment()
    {
        $this->quantity++;
    }
    
    public function decrement()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    public function render()
    {
        return view('livewire.add-to-cart-button');
    }
}


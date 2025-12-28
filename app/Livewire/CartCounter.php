<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;
    
    protected CartService $cartService;
    
    protected $listeners = ['cart-updated' => 'updateCount'];
    
    public function boot(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    
    public function mount()
    {
        $this->updateCount();
    }
    
    public function updateCount()
    {
        $this->count = $this->cartService->getTotalItems();
    }
    
    public function render()
    {
        return view('livewire.cart-counter');
    }
}


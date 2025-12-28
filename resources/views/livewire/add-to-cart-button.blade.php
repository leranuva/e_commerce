<div>
    @if($showQuantity ?? true)
        <div class="flex items-center space-x-3 mb-3">
            <div class="flex items-center border border-gray-300 overflow-hidden">
                <button 
                    wire:click="decrement"
                    class="px-4 py-3 text-slate-900 hover:text-slate-600 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="$quantity <= 1"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                </button>
                <input 
                    type="number" 
                    wire:model.live="quantity"
                    min="1"
                    :max="$wire.availableStock"
                    class="w-20 text-center border-0 focus:ring-0 font-light text-slate-900"
                />
                <button 
                    wire:click="increment"
                    class="px-4 py-3 text-slate-900 hover:text-slate-600 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="$quantity >= $wire.availableStock"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        </div>
    @endif
    
    <button 
        wire:click="addToCart"
        class="relative h-full w-full bg-transparent outline-none border-none text-base font-semibold tracking-wide text-white z-10 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="$wire.availableStock <= 0"
        x-bind:class="$wire.availableStock <= 0 ? 'opacity-50 cursor-not-allowed' : ''"
    >
        Add To Cart
    </button>
    
    @error('quantity')
        <p class="mt-3 text-sm text-red-600 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $message }}
        </p>
    @enderror
    
    @error('cart')
        <p class="mt-3 text-sm text-red-600 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $message }}
        </p>
    @enderror
    
    @if (session()->has('message'))
        <p class="mt-3 text-sm text-green-600 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('message') }}
        </p>
    @endif
    
    @if($this->availableStock <= 0)
        <p class="mt-3 text-sm text-red-600 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            Producto agotado
        </p>
    @endif
</div>


<livewire:cart-items lazy />

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Carrito de Compras</h1>
            @if(count($this->items) > 0)
                <button 
                    wire:click="clearCart"
                    wire:confirm="¿Estás seguro de que quieres vaciar el carrito?"
                    class="text-red-600 hover:text-red-800 text-sm"
                >
                    Vaciar Carrito
                </button>
            @endif
        </div>
        
        @if (session()->has('cart-message'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('cart-message') }}
            </div>
        @endif
        
        @error('cart')
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $message }}
            </div>
        @enderror
        
        @if(count($this->items) > 0)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="divide-y divide-gray-200">
                    @foreach($this->items as $item)
                        <div class="p-6 flex items-center space-x-6">
                            @if($item['image'])
                                <img 
                                    src="{{ $item['image'] }}" 
                                    alt="{{ $item['name'] }}"
                                    class="w-24 h-24 object-cover rounded-lg"
                                >
                            @else
                                <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $item['name'] }}</h3>
                                <p class="text-gray-600">${{ number_format($item['price'], 2) }} c/u</p>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center border border-gray-300 rounded-md">
                                    <button 
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                        class="px-3 py-2 text-gray-600 hover:text-gray-800"
                                        :disabled="$item['quantity'] <= 1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="px-4 py-2 text-gray-900">{{ $item['quantity'] }}</span>
                                    <button 
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                        class="px-3 py-2 text-gray-600 hover:text-gray-800"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-900">
                                        ${{ number_format($item['subtotal'], 2) }}
                                    </p>
                                </div>
                                
                                <button 
                                    wire:click="removeItem('{{ $item['id'] }}')"
                                    class="text-red-600 hover:text-red-800"
                                    title="Eliminar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>${{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>IVA (16%):</span>
                            <span>${{ number_format($this->tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-300">
                            <span>Total:</span>
                            <span>${{ number_format($this->total, 2) }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a 
                            href="{{ route('checkout.index') }}"
                            class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md text-center transition-colors"
                        >
                            Proceder al Checkout
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="mt-4 text-2xl font-semibold text-gray-900">Tu carrito está vacío</h2>
                <p class="mt-2 text-gray-600">Agrega productos para comenzar a comprar</p>
                <a 
                    href="{{ route('products.index') }}"
                    class="mt-6 inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md"
                >
                    Ver Productos
                </a>
            </div>
        @endif
    </div>
</div>


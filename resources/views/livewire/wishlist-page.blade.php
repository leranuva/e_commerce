<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Mi Lista de Deseos</h2>
        <p class="text-gray-600">Productos que has guardado para más tarde</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($wishlistItems as $item)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    @if($item->product->image_url)
                        <img 
                            src="{{ $item->product->image_url }}" 
                            alt="{{ $item->product->name }}"
                            class="w-full h-48 object-cover"
                        >
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">Sin imagen</span>
                        </div>
                    @endif
                    
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-2">{{ $item->product->name }}</h3>
                        
                        @if($item->variant)
                            <p class="text-sm text-gray-600 mb-2">
                                Variante: {{ $item->variant->sku }}
                            </p>
                        @endif

                        <p class="text-xl font-bold text-blue-600 mb-4">
                            ${{ number_format($item->variant?->effective_price ?? $item->product->price, 2) }}
                        </p>

                        <div class="flex gap-2">
                            <a 
                                href="{{ route('products.show', $item->product->id) }}"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700"
                            >
                                Ver Producto
                            </a>
                            <button 
                                wire:click="removeFromWishlist({{ $item->id }})"
                                class="px-4 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200"
                                title="Remover de lista"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $wishlistItems->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Tu lista de deseos está vacía</h3>
            <p class="mt-2 text-sm text-gray-500">Agrega productos que te gusten para verlos aquí</p>
            <a 
                href="{{ route('products.index') }}"
                class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                Explorar Productos
            </a>
        </div>
    @endif
</div>


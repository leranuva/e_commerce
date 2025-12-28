@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Breadcrumb -->
    <nav class="mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="{{ route('home') }}" class="hover:text-slate-900 transition-colors">Home</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="{{ route('products.index') }}" class="hover:text-slate-900 transition-colors">Shop</a></li>
            @if($product->category)
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:text-slate-900 transition-colors">{{ $product->category->name }}</a></li>
            @endif
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900 font-medium">{{ $product->name }}</li>
        </ol>
    </nav>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-16">
        <!-- Imagen del Producto -->
        <div class="space-y-4">
            <div class="bg-white border border-gray-200 overflow-hidden">
                @if($product->image_url)
                    <img 
                        src="{{ $product->image_url }}" 
                        alt="{{ $product->name }}"
                        class="w-full h-[600px] object-cover"
                    >
                @else
                    <div class="w-full h-[600px] bg-gradient-to-br from-gray-200 to-gray-300 rounded-2xl flex items-center justify-center">
                        <svg class="w-32 h-32 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Información del Producto -->
        <div class="space-y-6">
            @if($product->category)
                <div>
                    <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-slate-900 text-sm font-light uppercase tracking-wide hover:bg-gray-200 transition-colors">
                        {{ $product->category->name }}
                    </a>
                </div>
            @endif
            
            <div>
                <h1 class="text-4xl sm:text-5xl font-light text-slate-900 mb-4">{{ $product->name }}</h1>
                <div class="flex items-center space-x-4 mb-6">
                    <div>
                        <p class="text-4xl font-light text-slate-900">
                            ${{ number_format($product->price, 2) }}
                        </p>
                    </div>
                    @if($product->stock > 0)
                        <span class="px-3 py-1 text-xs text-gray-600 uppercase tracking-wide">
                            In Stock
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs text-red-600 uppercase tracking-wide">
                            Out of Stock
                        </span>
                    @endif
                </div>
            </div>
            
            @if($product->description)
                <div class="prose max-w-none">
                    <p class="text-gray-600 text-lg leading-relaxed font-light">{{ $product->description }}</p>
                </div>
            @endif
            
            <!-- Información Adicional -->
            <div class="bg-gray-50 border border-gray-200 p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 font-medium">SKU:</span>
                    <span class="text-gray-900 font-semibold">{{ $product->sku }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 font-medium">Disponibilidad:</span>
                    <span class="text-gray-900 font-semibold">
                        {{ $product->stock > 0 ? $product->stock . ' unidades disponibles' : 'Agotado' }}
                    </span>
                </div>
            </div>
            
            <!-- Variantes con Entangled State (Alpine.js) -->
            @if($product->variants->count() > 0)
                <div x-data="{ selectedVariant: null }" class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Variantes Disponibles</h3>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($product->variants as $variant)
                            <div 
                                class="border p-4 cursor-pointer transition-all duration-200"
                                :class="selectedVariant === {{ $variant->id }} ? 'border-slate-900 bg-gray-50' : 'border-gray-300 hover:border-slate-600'"
                                @click="selectedVariant = {{ $variant->id }}"
                            >
                                <p class="font-light text-slate-900 mb-1">{{ $variant->sku }}</p>
                                <p class="text-slate-900 font-light">${{ number_format($variant->price ?? $product->price, 2) }}</p>
                                <p class="text-sm text-gray-600 mt-1">Stock: {{ $variant->stock }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Botones de Acción -->
            <div class="space-y-4 pt-4">
                <livewire:add-to-cart-button :product="$product" />
                
                @auth('customer')
                    <div class="pt-4 border-t border-gray-200">
                        <livewire:wishlist-button :product="$product" />
                    </div>
                @endauth
            </div>
        </div>
    </div>
    
    <!-- Productos Relacionados -->
    @if($relatedProducts->count() > 0)
        <div class="mt-20">
            <div class="text-center mb-12">
                <p class="text-sm font-medium text-slate-600 uppercase tracking-wider mb-2">Related Products</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4">You May Also Like</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="group bg-white border border-gray-200 hover:border-slate-900 transition-all duration-300">
                        <div class="relative overflow-hidden bg-gray-50" style="aspect-ratio: 1 / 1;">
                            @if($relatedProduct->image_url)
                                <a href="{{ route('products.show', $relatedProduct) }}">
                                    <img 
                                        src="{{ $relatedProduct->image_url }}" 
                                        alt="{{ $relatedProduct->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                    >
                                </a>
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            @if($relatedProduct->category)
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ $relatedProduct->category->name }}</p>
                            @endif
                            <h3 class="text-base font-semibold text-slate-900 mb-2 line-clamp-2 group-hover:text-slate-600 transition-colors">
                                <a href="{{ route('products.show', $relatedProduct) }}">
                                    {{ $relatedProduct->name }}
                                </a>
                            </h3>
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-lg font-bold text-slate-900">${{ number_format($relatedProduct->price, 2) }}</p>
                                <div class="flex items-center text-yellow-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span class="text-xs text-gray-500 ml-1">0</span>
                                </div>
                            </div>
                            <livewire:add-to-cart-button :product="$relatedProduct" :show-quantity="false" lazy />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection


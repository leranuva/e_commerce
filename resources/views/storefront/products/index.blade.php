@extends('layouts.app')

@section('title', 'Productos')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');
    
    .product-card {
        position: relative;
        max-width: 100%;
        width: 100%;
        border-radius: 25px;
        padding: 20px 30px 30px 30px;
        background: #fff;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        z-index: 3;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
    }
    
    .product-card .logo-cart {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .product-card .main-images {
        position: relative;
        height: 200px;
        overflow: hidden;
    }
    
    .product-card .main-images img {
        position: absolute;
        height: 280px;
        width: 280px;
        object-fit: cover;
        transform: rotate(18deg);
        left: 12px;
        top: -40px;
        z-index: -1;
        transition: opacity 0.5s ease;
    }
    
    .product-card .shoe-details .shoe_name {
        font-size: 20px;
        font-weight: 500;
        color: #161616;
    }
    
    .product-card .shoe-details p {
        font-size: 12px;
        font-weight: 400;
        color: #333;
        text-align: justify;
    }
    
    .product-card .shoe-details .stars svg {
        margin: 0 -1px;
    }
    
    .color-price {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }
    
    .color-price .color-option .color {
        font-size: 16px;
        font-weight: 500;
        color: #333;
        margin-right: 8px;
    }
    
    .color-option .circles {
        display: flex;
    }
    
    .color-option .circles .circle {
        height: 18px;
        width: 18px;
        border-radius: 50%;
        margin: 0 4px;
        cursor: pointer;
        transition: all 0.4s ease;
    }
    
    .color-price .price {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-end;
    }
    
    .color-price .price .price_num {
        font-size: 24px;
        font-weight: 600;
        color: #707070;
    }
    
    .color-price .price .price_letter {
        font-size: 10px;
        font-weight: 600;
        margin-top: -4px;
        color: #707070;
    }
    
    .product-card .button {
        position: relative;
        height: 50px;
        width: 100%;
        border-radius: 25px;
        margin-top: 20px;
        overflow: hidden;
    }
    
    .product-card .button .button-layer {
        position: absolute;
        height: 100%;
        width: 300%;
        left: -100%;
        background-image: linear-gradient(135deg, #9708CC, #43CBFF, #9708CC, #43CBFF);
        transition: all 0.4s ease;
        border-radius: 25px;
    }
    
    .product-card .button:hover .button-layer {
        left: 0;
    }
    
    .product-card .button button {
        position: relative;
        height: 100%;
        width: 100%;
        background: none;
        outline: none;
        border: none;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 1px;
        color: #fff;
        cursor: pointer;
        z-index: 10;
    }
    
    .product-card .button button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
@if(request()->routeIs('home'))
    <!-- Hero Section - Cosmetics Store Style -->
    <section class="relative bg-white overflow-hidden flex items-center" style="height: 60vh;">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=1920&auto=format&fit=crop');">
            <!-- Overlay oscuro para contraste y legibilidad -->
            <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.5));"></div>
        </div>
        
        <!-- Content -->
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-sm text-white font-medium text-slate-700 uppercase tracking-wider mb-4 drop-shadow-sm">A Fresh Perspective</p>
                <h1 class="text-5xl sm:text-6xl md:text-7xl font-bold text-white mb-6 leading-tight drop-shadow-md">
                    The Art of Revelation Beauty
                </h1>
                <p class="text-lg text-white sm:text-xl text-slate-700 mb-10 max-w-2xl mx-auto leading-relaxed drop-shadow-sm">
                   We create skincare and color formulas designed to amplify your inner glow, bringing confidence to your every gesture. A delicate, polished, and genuinely luminous aesthetic—this is beauty at its finest.
                </p>
                <a href="{{ route('products.index') }}" class="group inline-flex items-center px-10 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white font-medium text-sm uppercase tracking-[0.15em] hover:bg-white/20 hover:border-white/50 transition-all duration-500 shadow-xl hover:shadow-2xl hover:scale-105">
                    <span class="relative">View More</span>
                    <svg class="ml-3 w-5 h-5 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section - Cosmetics Store Style -->
    @if($featuredProducts && $featuredProducts->count() > 0)
    <section id="featured" class="bg-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-medium text-slate-600 uppercase tracking-wider mb-2">The Flawless Finish</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4">Define Your Girl Era</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Explore and Make Your Presence Felt</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach($featuredProducts->take(8) as $product)
                <div class="product-card" x-data="{ selectedVariant: null, activeImage: '{{ $product->image_url ?? '' }}' }">
                    <!-- Logo and Cart -->
                    <div class="logo-cart flex items-center justify-between mb-4">
                        <div class="h-12 w-12 rounded-lg bg-slate-900 flex items-center justify-center">
                            <span class="text-white font-bold text-lg">E</span>
                        </div>
                        <a href="{{ route('products.show', $product) }}" class="text-gray-600 hover:text-slate-900 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Main Images -->
                    <div class="main-images relative h-48 mb-4 flex items-center justify-center">
                        @if($product->image_url)
                            <img 
                                :src="activeImage || '{{ $product->image_url }}'"
                                alt="{{ $product->name }}"
                                class="absolute h-64 w-64 object-contain transform rotate-12 transition-opacity duration-500"
                                :class="activeImage ? 'opacity-100' : 'opacity-0'"
                                style="left: 12px; top: -20px; z-index: -1;"
                            >
                            <img 
                                src="{{ $product->image_url }}"
                                alt="{{ $product->name }}"
                                class="absolute h-64 w-64 object-contain transform rotate-12 transition-opacity duration-500 opacity-100"
                                style="left: 12px; top: -20px; z-index: -1;"
                                x-show="!activeImage || activeImage === '{{ $product->image_url }}'"
                            >
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center rounded-lg">
                                <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Product Details -->
                    <div class="shoe-details mb-4">
                        <h3 class="shoe_name text-xl font-semibold text-slate-900 mb-2 line-clamp-1">
                            <a href="{{ route('products.show', $product) }}" class="hover:text-slate-600 transition-colors">{{ $product->name }}</a>
                        </h3>
                        <p class="text-xs text-gray-600 text-justify line-clamp-2 mb-3">
                            {{ \Illuminate\Support\Str::limit($product->description ?? 'Premium quality product', 80) }}
                        </p>
                        <div class="stars flex items-center">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="w-4 h-4 {{ $i < 4 ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    
                    <!-- Color and Price -->
                    @if($product->variants->count() > 0)
                    <div class="color-price flex justify-between items-center mb-4">
                        <div class="color-option flex items-center">
                            <span class="color text-base font-medium text-slate-900 mr-2">Color:</span>
                            <div class="circles flex">
                                @foreach($product->variants->take(3) as $index => $variant)
                                    @php
                                        $colors = ['#0071C7', '#FA1795', '#F5DA00', '#10B981', '#8B5CF6'];
                                        $color = $colors[$index % count($colors)];
                                    @endphp
                                    <span 
                                        class="circle h-4 w-4 rounded-full cursor-pointer transition-all duration-400 mx-1"
                                        style="background: {{ $color }};"
                                        :class="$index === 0 ? 'active' : ''"
                                        @click="selectedVariant = {{ $variant->id }}; activeImage = '{{ $variant->getFirstMediaUrl('images') ?: $product->image_url }}'"
                                        x-bind:class="selectedVariant === {{ $variant->id }} ? 'active' : ''"
                                        x-bind:style="selectedVariant === {{ $variant->id }} ? 'box-shadow: 0 0 0 2px #fff, 0 0 0 4px {{ $color }};' : ''"
                                    ></span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="color-price flex justify-between items-center mb-4">
                        <div class="price flex flex-col items-end">
                            <span class="price_num text-2xl font-semibold text-slate-700">${{ number_format($product->price, 2) }}</span>
                            <span class="price_letter text-xs font-semibold text-slate-500 -mt-1">USD</span>
                        </div>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <div class="button relative h-12 w-full rounded-full mt-6 overflow-hidden group">
                        <div class="button-layer absolute h-full w-[300%] -left-full transition-all duration-400 rounded-full group-hover:left-0" style="background-image: linear-gradient(135deg, #9708CC, #43CBFF, #9708CC, #43CBFF);"></div>
                        <livewire:add-to-cart-button :product="$product" :show-quantity="false" lazy />
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Features Section - Cosmetics Store Style -->
    <section class="bg-gray-50 py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Season Sale -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-full mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Season Sale</h3>
                    <p class="text-gray-600">Enjoy exclusive discounts on your favorite beauty essentials. Refresh your look with deals you'll love this season.</p>
                </div>

                <!-- Free Shipping -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-full mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 011 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V10a2 2 0 011-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Free Shipping</h3>
                    <p class="text-gray-600">Get your glam delivered hassle-free. Enjoy free shipping and experience the joy of beauty arriving right at your doorstep.</p>
                </div>

                <!-- Money Back Guarantee -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-900 rounded-full mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Money Back Guarantee</h3>
                    <p class="text-gray-600">Shop with total confidence. If you're not fully satisfied, we'll refund your purchase - your happiness matters most.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Be Bold Section - Cosmetics Store Style -->
    <section class="bg-slate-900 text-white py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto">
                <p class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">Be Bold, Be Daring</p>
                <h2 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6 leading-tight">A Whole New Look</h2>
                <p class="text-lg sm:text-xl text-gray-300 mb-10 max-w-2xl mx-auto leading-relaxed">
                    Unveil a look that's uniquely yours. Step out, stand tall, and redefine beauty on your own terms.
                </p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-8 py-4 bg-white text-slate-900 font-semibold text-sm uppercase tracking-wide hover:bg-gray-100 transition-all duration-300">
                    View More
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section - Cosmetics Store Style -->
    <section class="bg-white py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-medium text-slate-600 uppercase tracking-wider mb-2">Testimonials</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4">Our Happy Clients</h2>
            </div>
            
            <div class="max-w-3xl mx-auto">
                <div class="bg-gray-50 p-8 lg:p-12 rounded-lg text-center">
                    <div class="flex justify-center mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 0; $i < 5; $i++)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                        "I've never felt more confident in my skin! The products are gentle, luxurious, and truly deliver what they promise. Ecommerce has become my go-to for everyday beauty."
                    </p>
                    <p class="text-base font-semibold text-slate-900">Marilyn Keller</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Products Section - Cosmetics Store Style -->
    @if($featuredProducts && $featuredProducts->count() > 4)
    <section class="bg-gray-50 py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-medium text-slate-600 uppercase tracking-wider mb-2">Blossom into a New You!</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4">Latest Products</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Discover our latest arrivals — fresh, bold, and made to enhance your natural beauty.</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts->skip(4)->take(4) as $product)
                <div class="group bg-white border border-gray-200 hover:border-slate-900 transition-all duration-300">
                    <div class="relative overflow-hidden bg-gray-50" style="aspect-ratio: 1 / 1;">
                        @if($product->image_url)
                            <a href="{{ route('products.show', $product) }}">
                                <img 
                                    src="{{ $product->image_url }}" 
                                    alt="{{ $product->name }}"
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
                    <div class="p-5 bg-white">
                        @if($product->category)
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ $product->category->name }}</p>
                        @endif
                        <h3 class="text-base font-semibold text-slate-900 mb-2 group-hover:text-slate-600 transition-colors line-clamp-2">
                            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                        </h3>
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-lg font-bold text-slate-900">${{ number_format($product->price, 2) }}</p>
                            <div class="flex items-center text-yellow-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <span class="text-xs text-gray-500 ml-1">0</span>
                            </div>
                        </div>
                        <livewire:add-to-cart-button :product="$product" :show-quantity="false" lazy />
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- All Products Section -->
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl sm:text-5xl font-light text-slate-900 mb-4">Our Products</h2>
                <div class="w-24 h-0.5 bg-slate-900 mx-auto"></div>
            </div>
@endif

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar de Filtros -->
        <aside class="lg:w-64 flex-shrink-0">
            <div class="bg-white shadow-lg p-6 sticky top-24 border border-gray-200">
                <h2 class="text-xl font-semibold text-slate-900 mb-6 uppercase tracking-wide">Filtros</h2>
                
                <form method="GET" action="{{ route('products.index') }}" class="space-y-5">
                    <!-- Búsqueda -->
                    <div>
                        <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Search for:</label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar productos..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all"
                            >
                            <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Categorías -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Categoría</label>
                        <select 
                            id="category"
                            name="category"
                            class="w-full py-2.5 px-4 border border-gray-300 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Ordenamiento -->
                    <div>
                        <label for="sort" class="block text-sm font-semibold text-gray-700 mb-2">Ordenar por</label>
                        <select 
                            id="sort"
                            name="sort"
                            class="w-full py-2.5 px-4 border border-gray-300 focus:ring-2 focus:ring-slate-900 focus:border-slate-900 transition-all bg-white"
                            onchange="this.form.submit()"
                        >
                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Más recientes</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nombre A-Z</option>
                            <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-4 transition-all duration-200 uppercase tracking-wide text-sm">
                        Aplicar Filtros
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Lista de Productos -->
        <div class="flex-1">
            @if(!request()->routeIs('home'))
                <div class="mb-12 text-center">
                    <p class="text-sm font-medium text-slate-600 uppercase tracking-wider mb-2">Shop</p>
                    <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-4">Our Products</h1>
                    <p class="text-gray-600 max-w-2xl mx-auto">Discover our complete collection of beauty essentials.</p>
                </div>
            @endif
            
            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        <div class="group bg-white border border-gray-200 hover:border-slate-900 transition-all duration-300">
                            <!-- Product Image -->
                            <div class="relative overflow-hidden bg-gray-50" style="aspect-ratio: 1 / 1;">
                                @if($product->image_url)
                                    <a href="{{ route('products.show', $product) }}">
                                        <img 
                                            src="{{ $product->image_url }}" 
                                            alt="{{ $product->name }}"
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
                            
                            <!-- Product Info -->
                            <div class="p-5 bg-white">
                                @if($product->category)
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ $product->category->name }}</p>
                                @endif
                                <h3 class="text-base font-semibold text-slate-900 mb-2 group-hover:text-slate-600 transition-colors line-clamp-2">
                                    <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                </h3>
                                <div class="flex items-center justify-between mb-3">
                                    <p class="text-lg font-bold text-slate-900">${{ number_format($product->price, 2) }}</p>
                                    <div class="flex items-center text-yellow-400">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="text-xs text-gray-500 ml-1">0</span>
                                    </div>
                                </div>
                                <livewire:add-to-cart-button :product="$product" :show-quantity="false" lazy />
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Paginación -->
                <div class="mt-12">
                    {{ $products->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-white border border-gray-200">
                    <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xl font-semibold text-gray-700 mb-2">No se encontraron productos</p>
                    <p class="text-gray-500">Intenta ajustar tus filtros de búsqueda</p>
                </div>
            @endif
        </div>
    </div>
</div>
@if(request()->routeIs('home'))
    </section>
@endif
@endsection

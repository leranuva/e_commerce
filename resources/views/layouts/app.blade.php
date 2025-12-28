<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Tienda Online')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    @stack('scripts')
</head>
<body class="bg-gray-50 antialiased">
    
    <!-- Main Navigation - Cosmetics Store Style -->
    <nav class="bg-white sticky top-0 z-50 shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-slate-900 hover:text-slate-700 transition-colors">
                        Ecommerce
                    </a>
                </div>

                <!-- Navigation Links - Centered -->
                <div class="flex-1 flex justify-center">
                    <ul class="flex items-center space-x-8">
                        <li>
                            <a href="{{ route('home') }}" class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors {{ request()->routeIs('home') ? 'text-slate-900 font-semibold' : '' }}">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('products.index') }}" class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors {{ request()->routeIs('products.*') ? 'text-slate-900 font-semibold' : '' }}">
                                Shop
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                                Testimonials
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                                About
                            </a>
                        </li>
                        <li>
                            <a href="#" class="text-sm font-medium text-slate-700 hover:text-slate-900 transition-colors">
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    @auth('customer')
                        <a href="{{ route('wishlist') }}" class="p-2 text-slate-700 hover:text-slate-900 transition-colors" aria-label="Wishlist">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </a>
                    @endauth
                    <livewire:cart-counter />
                    @auth('customer')
                        <a href="{{ route('wishlist') }}" class="p-2 text-slate-700 hover:text-slate-900 transition-colors" aria-label="Account">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    @else
                        <a href="{{ route('customer.login') }}" class="p-2 text-slate-700 hover:text-slate-900 transition-colors" aria-label="Login">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="lg:hidden">
                <div class="flex items-center justify-between h-16">
                    <a href="{{ route('home') }}" class="text-xl font-bold text-slate-900">
                        Ecommerce
                    </a>
                    <div class="flex items-center space-x-3">
                        <livewire:cart-counter />
                        <button 
                            @click="mobileMenuOpen = !mobileMenuOpen" 
                            class="p-2 text-slate-900"
                            aria-label="Toggle navigation"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div 
                x-show="mobileMenuOpen" 
                x-cloak
                x-transition
                @click.away="mobileMenuOpen = false"
                class="lg:hidden border-t border-gray-100 bg-white"
            >
                <div class="px-4 py-4 space-y-2">
                    <a href="{{ route('home') }}" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900 {{ request()->routeIs('home') ? 'text-slate-900 font-semibold' : '' }}">
                        Home
                    </a>
                    <a href="{{ route('products.index') }}" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900 {{ request()->routeIs('products.*') ? 'text-slate-900 font-semibold' : '' }}">
                        Shop
                    </a>
                    <a href="#" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Testimonials
                    </a>
                    <a href="#" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        About
                    </a>
                    <a href="#" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                        Contact
                    </a>
                    @auth('customer')
                        <div class="border-t border-gray-100 mt-2 pt-2">
                            <a href="{{ route('wishlist') }}" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                                Wishlist
                            </a>
                            <form method="POST" action="{{ route('customer.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="border-t border-gray-100 mt-2 pt-2">
                            <a href="{{ route('customer.login') }}" class="block py-2 text-sm font-medium text-slate-700 hover:text-slate-900">
                                Login
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>
    
    <!-- Newsletter Section - Cosmetics Store Style -->
    <section class="bg-slate-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-2xl sm:text-3xl font-bold mb-4">Sign-up the Makeup Fan Club</h2>
                <form class="max-w-md mx-auto flex gap-2">
                    <input 
                        type="email" 
                        placeholder="Website" 
                        class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-white/50"
                    >
                    <button type="submit" class="px-6 py-3 bg-white text-slate-900 font-semibold uppercase tracking-wide text-sm hover:bg-gray-100 transition-colors">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer - Cosmetics Store Style -->
    <footer class="bg-slate-900 text-white border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <!-- Contact Details -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 text-gray-300">Contact Details</h3>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li>929-242-6868</li>
                        <li>info@ecommerce.com</li>
                        <li>123 Fifth Avenue, New York, NY 10160</li>
                    </ul>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 text-gray-300">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Shipping & Returns</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Contact</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Customer Service</a></li>
                    </ul>
                </div>
                
                <!-- Social Media -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider mb-4 text-gray-300">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} Cosmetics Store | Powered by Ecommerce</p>
            </div>
        </div>
    </footer>
    
    @livewireScripts
</body>
</html>


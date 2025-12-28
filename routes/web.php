<?php

use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

// Storefront (Público)
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/products', [StorefrontController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [StorefrontController::class, 'show'])->name('products.show');

// Carrito
Route::get('/cart', \App\Livewire\CartItems::class)->name('cart.index');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/processing/{order}', [CheckoutController::class, 'processing'])->name('checkout.processing');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

// Webhook de Stripe (excluido de CSRF)
Route::post('/webhooks/stripe', [App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('webhooks.stripe')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Autenticación de Clientes
Route::get('/customer/login', [App\Http\Controllers\Auth\CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
Route::post('/customer/login', [App\Http\Controllers\Auth\CustomerAuthController::class, 'login']);
Route::get('/customer/register', [App\Http\Controllers\Auth\CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
Route::post('/customer/register', [App\Http\Controllers\Auth\CustomerAuthController::class, 'register']);
Route::post('/customer/logout', [App\Http\Controllers\Auth\CustomerAuthController::class, 'logout'])->name('customer.logout');

// Wishlist (requiere autenticación)
Route::get('/wishlist', \App\Livewire\WishlistPage::class)->name('wishlist')
    ->middleware('auth:customer');

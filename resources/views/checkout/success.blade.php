@extends('layouts.app')

@section('title', 'Orden Confirmada')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <div class="mb-6">
            <svg class="mx-auto h-16 w-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-4">¡Orden Confirmada!</h1>
        <p class="text-gray-600 mb-6">Tu orden #{{ $order->id }} ha sido procesada exitosamente.</p>
        
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de la Orden</h2>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span class="font-medium">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">IVA:</span>
                    <span class="font-medium">${{ number_format($order->tax, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Envío:</span>
                    <span class="font-medium">${{ number_format($order->shipping_cost, 2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t pt-2">
                    <span>Total:</span>
                    <span>${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
            
            <div class="mt-4">
                <p class="text-sm text-gray-600"><strong>Estado:</strong> {{ $order->status_label }}</p>
            </div>
        </div>
        
        <div class="flex justify-center space-x-4">
            <a 
                href="{{ route('products.index') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md"
            >
                Continuar Comprando
            </a>
        </div>
    </div>
</div>
@endsection


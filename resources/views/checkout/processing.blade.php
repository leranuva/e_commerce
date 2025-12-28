@extends('layouts.app')

@section('title', 'Procesando Pago')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white shadow rounded-lg p-8 text-center">
        <div class="mb-6">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Procesando tu Pago</h1>
        <p class="text-gray-600 mb-6">Estamos confirmando tu pago. Por favor espera unos segundos...</p>
        
        <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles de la Orden</h2>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Número de Orden:</span>
                    <span class="font-medium">#{{ $order->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Total:</span>
                    <span class="font-medium">${{ number_format($order->total, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Estado:</span>
                    <span class="font-medium">{{ $order->status_label }}</span>
                </div>
            </div>
        </div>
        
        <p class="text-sm text-gray-500">
            Esta página se actualizará automáticamente cuando tu pago sea confirmado.
        </p>
    </div>
</div>

@push('scripts')
<script>
    // Polling para verificar si la orden fue confirmada
    let pollCount = 0;
    const maxPolls = 30; // 30 intentos (30 segundos)
    
    const checkOrderStatus = setInterval(() => {
        pollCount++;
        
        fetch('{{ route('checkout.success', $order->id) }}', {
            method: 'HEAD',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => {
            if (response.ok) {
                // Orden confirmada, redirigir
                clearInterval(checkOrderStatus);
                window.location.href = '{{ route('checkout.success', $order->id) }}';
            }
        })
        .catch(() => {
            // Error en la petición, continuar polling
        });
        
        if (pollCount >= maxPolls) {
            clearInterval(checkOrderStatus);
            // Mostrar mensaje de timeout
            alert('El procesamiento está tomando más tiempo del esperado. Por favor, verifica el estado de tu orden más tarde.');
        }
    }, 1000); // Verificar cada segundo
</script>
@endpush
@endsection


<div>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Checkout</h1>
        
        @error('order')
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $message }}
            </div>
        @enderror
        
        @error('payment')
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $message }}
            </div>
        @enderror
        
        @if(count($this->items) > 0)
            @if(!$showPaymentForm)
                <!-- Paso 1: Información de Cliente y Envío -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información del Cliente (Guest Checkout) -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de Contacto</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                                    <input 
                                        type="text" 
                                        id="customer_name"
                                        wire:model="customerData.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                        required
                                    >
                                    @error('customerData.name') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input 
                                        type="email" 
                                        id="customer_email"
                                        wire:model="customerData.email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                        required
                                    >
                                    @error('customerData.email') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700">Teléfono (Opcional)</label>
                                    <input 
                                        type="tel" 
                                        id="customer_phone"
                                        wire:model="customerData.phone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                    >
                                    @error('customerData.phone') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                @guest('customer')
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            id="createAccount"
                                            wire:model.live="createAccount"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        <label for="createAccount" class="ml-2 text-sm text-gray-700">
                                            Crear una cuenta para compras futuras
                                        </label>
                                    </div>
                                    
                                    @if($createAccount)
                                        <div class="space-y-4" x-show="$wire.createAccount" x-transition>
                                            <div>
                                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                                                <input 
                                                    type="password" 
                                                    id="password"
                                                    wire:model="password"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                                >
                                                @error('password') 
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                                @enderror
                                            </div>
                                            
                                            <div>
                                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                                <input 
                                                    type="password" 
                                                    id="password_confirmation"
                                                    wire:model="password_confirmation"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                                >
                                            </div>
                                        </div>
                                    @endif
                                @endguest
                            </div>
                        </div>
                        
                        <!-- Información de Envío -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de Envío</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">Dirección</label>
                                    <input 
                                        type="text" 
                                        id="address"
                                        wire:model="shippingData.address"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                        required
                                    >
                                    @error('shippingData.address') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700">Ciudad</label>
                                    <input 
                                        type="text" 
                                        id="city"
                                        wire:model="shippingData.city"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                        required
                                    >
                                    @error('shippingData.city') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700">Código Postal</label>
                                    <input 
                                        type="text" 
                                        id="postal_code"
                                        wire:model="shippingData.postal_code"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                                        required
                                    >
                                    @error('shippingData.postal_code') 
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <button 
                            wire:click="proceedToPayment"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
                        >
                            Proceder al Pago
                        </button>
                    </div>
                    
                    <!-- Resumen del Pedido -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow rounded-lg p-6 sticky top-4">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Resumen del Pedido</h2>
                            
                            <div class="space-y-3 mb-4">
                                @foreach($this->items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                                        <span class="text-gray-900">${{ number_format($item['subtotal'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 space-y-2">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format($this->subtotal, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>IVA (16%):</span>
                                    <span>${{ number_format($this->tax, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-gray-600">
                                    <span>Envío:</span>
                                    <span>${{ number_format($this->shipping, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-300">
                                    <span>Total:</span>
                                    <span>${{ number_format($this->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Paso 2: Pago con Stripe -->
                <div class="max-w-2xl mx-auto">
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Información de Pago</h2>
                        
                        <!-- Stripe Elements Container -->
                        <div id="stripe-card-element" class="mb-4">
                            <!-- Stripe Elements se montará aquí -->
                        </div>
                        
                        <div id="stripe-card-errors" class="text-red-600 text-sm mb-4"></div>
                        
                        <button 
                            id="submit-payment"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
                        >
                            Confirmar y Pagar ${{ number_format($this->total, 2) }}
                        </button>
                    </div>
                </div>
                
                <!-- Stripe Scripts -->
                @push('scripts')
                <script src="https://js.stripe.com/v3/"></script>
                <script>
                    document.addEventListener('livewire:init', () => {
                        const stripe = Stripe('{{ config('services.stripe.key') }}');
                        const elements = stripe.elements();
                        
                        const cardElement = elements.create('card', {
                            style: {
                                base: {
                                    fontSize: '16px',
                                    color: '#424770',
                                    '::placeholder': {
                                        color: '#aab7c4',
                                    },
                                },
                            },
                        });
                        
                        cardElement.mount('#stripe-card-element');
                        
                        cardElement.on('change', ({error}) => {
                            const displayError = document.getElementById('stripe-card-errors');
                            if (error) {
                                displayError.textContent = error.message;
                            } else {
                                displayError.textContent = '';
                            }
                        });
                        
                        document.getElementById('submit-payment').addEventListener('click', async (e) => {
                            e.preventDefault();
                            
                            const submitButton = document.getElementById('submit-payment');
                            submitButton.disabled = true;
                            submitButton.textContent = 'Procesando...';
                            
                            const {error, paymentIntent} = await stripe.confirmCardPayment(
                                '{{ $paymentIntentClientSecret }}',
                                {
                                    payment_method: {
                                        card: cardElement,
                                    },
                                }
                            );
                            
                            if (error) {
                                document.getElementById('stripe-card-errors').textContent = error.message;
                                submitButton.disabled = false;
                                submitButton.textContent = 'Confirmar y Pagar ${{ number_format($this->total, 2) }}';
                            } else if (paymentIntent.status === 'succeeded') {
                                // Pago exitoso, notificar a Livewire y redirigir
                                @this.handlePaymentSuccess();
                            }
                        });
                    });
                </script>
                @endpush
            @endif
        @else
            <div class="text-center py-12">
                <p class="text-gray-600 mb-4">Tu carrito está vacío</p>
                <a 
                    href="{{ route('products.index') }}"
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md"
                >
                    Ver Productos
                </a>
            </div>
        @endif
    </div>
</div>

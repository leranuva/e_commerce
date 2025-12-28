<?php

namespace App\Livewire;

use App\Domains\Sales\Actions\CreateOrderAction;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Domains\Customers\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Checkout extends Component
{
    protected CartService $cartService;
    protected PaymentService $paymentService;
    
    // Datos de envío
    public $shippingData = [
        'address' => '',
        'city' => '',
        'postal_code' => '',
    ];
    
    // Datos de cliente (para Guest Checkout)
    public $customerData = [
        'name' => '',
        'email' => '',
        'phone' => '',
    ];
    
    // Datos de cuenta (si no está autenticado)
    public $createAccount = false;
    public $password = '';
    public $password_confirmation = '';
    
    // Pago
    public $paymentIntentClientSecret = null;
    public $paymentIntentId = null;
    public $showPaymentForm = false;
    
    public $useDefaultAddress = false;
    
    protected $rules = [
        'shippingData.address' => 'required|string|max:255',
        'shippingData.city' => 'required|string|max:255',
        'shippingData.postal_code' => 'required|string|max:255',
        'customerData.name' => 'required|string|max:255',
        'customerData.email' => 'required|email|max:255',
        'customerData.phone' => 'nullable|string|max:255',
        'password' => 'required_if:createAccount,true|confirmed|min:8',
    ];
    
    public function boot(CartService $cartService, PaymentService $paymentService)
    {
        $this->cartService = $cartService;
        $this->paymentService = $paymentService;
    }
    
    public function mount()
    {
        // Si el usuario está autenticado, cargar datos
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            
            // Cargar datos del cliente
            $this->customerData = [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '',
            ];
            
            // Cargar dirección predeterminada
            $defaultAddress = $customer->defaultAddress();
            if ($defaultAddress) {
                $this->shippingData = [
                    'address' => $defaultAddress->street,
                    'city' => $defaultAddress->city,
                    'postal_code' => $defaultAddress->postal_code,
                ];
                $this->useDefaultAddress = true;
            }
        }
    }
    
    public function getItemsProperty(): array
    {
        return $this->cartService->getItemsWithProducts();
    }
    
    public function getSubtotalProperty(): float
    {
        return $this->cartService->getSubtotal();
    }
    
    public function getTaxProperty(): float
    {
        return $this->subtotal * 0.16;
    }
    
    public function getShippingProperty(): float
    {
        // Costo fijo de envío (puede mejorarse con lógica más compleja)
        return 50.00;
    }
    
    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->tax + $this->shipping;
    }
    
    /**
     * Proceder al pago (Guest Checkout habilitado).
     * 
     * CRÍTICO: Crea una orden PENDIENTE con payment_intent_id.
     * La orden solo se confirma cuando el webhook de Stripe confirma el pago.
     */
    public function proceedToPayment()
    {
        // Validar datos de envío y cliente
        $this->validate([
            'shippingData.address' => 'required|string|max:255',
            'shippingData.city' => 'required|string|max:255',
            'shippingData.postal_code' => 'required|string|max:255',
            'customerData.name' => 'required|string|max:255',
            'customerData.email' => 'required|email|max:255',
            'customerData.phone' => 'nullable|string|max:255',
            'password' => 'required_if:createAccount,true|confirmed|min:8',
        ]);
        
        // Verificar que el carrito no esté vacío
        if (empty($this->items)) {
            $this->addError('cart', 'Tu carrito está vacío');
            return;
        }
        
        try {
            // Obtener o crear cliente
            $customer = $this->getOrCreateCustomer();
            
            // Crear PaymentIntent con Stripe PRIMERO
            $paymentData = $this->paymentService->createPaymentIntent(
                $this->total,
                'usd',
                [
                    'customer_name' => $this->customerData['name'],
                    'customer_email' => $this->customerData['email'],
                ]
            );
            
            $this->paymentIntentId = $paymentData['payment_intent_id'];
            
            // CRÍTICO: Crear orden PENDIENTE con payment_intent_id
            // Esta orden NO se confirma hasta que el webhook lo haga
            $cartItems = collect($this->items)->map(function ($item) {
                return [
                    'product_id' => $item['product']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ];
            })->toArray();
            
            // Crear orden pendiente vinculada al PaymentIntent
            $order = CreateOrderAction::run($customer, $cartItems, $this->shippingData, $this->paymentIntentId);
            
            // Guardar order_id en sesión para redirección después del pago
            session()->put('pending_order_id', $order->id);
            
            $this->paymentIntentClientSecret = $paymentData['client_secret'];
            $this->showPaymentForm = true;
            
        } catch (\Exception $e) {
            $this->addError('payment', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesar pago confirmado por Stripe.
     * 
     * NOTA: Esta función solo se llama cuando Stripe confirma el pago en el cliente.
     * La orden REAL se confirma mediante webhook de Stripe (StripeWebhookController).
     * 
     * Aquí solo verificamos que el pago fue procesado y redirigimos a una página
     * de "esperando confirmación" o directamente a éxito si el webhook ya procesó.
     */
    public function handlePaymentSuccess()
    {
        if (!$this->paymentIntentId) {
            $this->addError('payment', 'No hay un pago pendiente');
            return;
        }
        
        try {
            // Verificar estado del pago
            $paymentStatus = $this->paymentService->getPaymentStatus($this->paymentIntentId);
            
            if ($paymentStatus !== 'succeeded') {
                $this->addError('payment', 'El pago no ha sido confirmado. Por favor, intenta nuevamente.');
                return;
            }
            
            // Buscar la orden pendiente
            $order = Order::where('payment_intent_id', $this->paymentIntentId)->first();
            
            if (!$order) {
                $this->addError('order', 'No se encontró la orden asociada al pago.');
                return;
            }
            
            // Verificar si el webhook ya confirmó la orden
            // Si el estado es 'paid', el webhook ya procesó la orden
            if ($order->status === OrderStatus::PAID) {
                // Orden ya confirmada por webhook, limpiar carrito y redirigir
                $this->cartService->clear();
                $this->dispatch('cart-updated');
                
                return redirect()->route('checkout.success', $order->id)
                    ->with('success', '¡Orden confirmada exitosamente!');
            }
            
            // Si aún está pendiente, esperar a que el webhook la confirme
            // Mostrar página de "procesando pago"
            return redirect()->route('checkout.processing', $order->id)
                ->with('info', 'Procesando tu pago. Por favor espera...');
                
        } catch (\Exception $e) {
            $this->addError('order', 'Error al procesar la orden: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener o crear cliente (Guest Checkout).
     */
    protected function getOrCreateCustomer(): Customer
    {
        // Si ya está autenticado, retornar el cliente actual
        if (Auth::guard('customer')->check()) {
            return Auth::guard('customer')->user();
        }
        
        // Buscar cliente por email
        $customer = Customer::where('email', $this->customerData['email'])->first();
        
        if ($customer) {
            // Si existe pero no está autenticado, autenticarlo
            if (!$this->createAccount) {
                Auth::guard('customer')->login($customer);
            }
            return $customer;
        }
        
        // Crear nuevo cliente si se solicita cuenta
        if ($this->createAccount) {
            $customer = Customer::create([
                'name' => $this->customerData['name'],
                'email' => $this->customerData['email'],
                'password' => Hash::make($this->password),
                'phone' => $this->customerData['phone'] ?? null,
            ]);
            
            Auth::guard('customer')->login($customer);
            
            return $customer;
        }
        
        // Guest checkout: crear cliente temporal sin password
        // Nota: Esto requiere modificar la migración para hacer password nullable
        // Por ahora, creamos con password temporal
        $customer = Customer::create([
            'name' => $this->customerData['name'],
            'email' => $this->customerData['email'],
            'password' => Hash::make(uniqid()), // Password temporal
            'phone' => $this->customerData['phone'] ?? null,
        ]);
        
        return $customer;
    }
    
    public function render()
    {
        return view('livewire.checkout');
    }
}


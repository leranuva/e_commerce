# Actions vs Services: Guía de Arquitectura

## 📋 Introducción

En proyectos de e-commerce de gran envergadura, es crucial entender cuándo usar **Actions** y cuándo usar **Services**. Esta guía explica las diferencias, casos de uso y mejores prácticas.

## 🎯 Actions (Enfoque Actual del Proyecto)

### ¿Qué son las Actions?

Las **Actions** son clases que encapsulan una **única operación de negocio** con un propósito específico. Siguen el principio de responsabilidad única y son fáciles de testear.

### Características

- ✅ **Un solo propósito**: Cada Action hace una cosa y la hace bien
- ✅ **Sin estado**: No mantienen estado entre llamadas
- ✅ **Fáciles de testear**: Lógica aislada y predecible
- ✅ **Reutilizables**: Pueden ser llamadas desde múltiples lugares
- ✅ **Transaccionales**: Manejan operaciones complejas de forma segura

### Estructura

```php
<?php

namespace App\Domains\Sales\Actions;

use App\Actions\Action;
use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;

class ChangeOrderStatusAction extends Action
{
    /**
     * Cambia el estado de una orden.
     *
     * @param Order $order
     * @param OrderStatus $newStatus
     * @return Order
     */
    public function execute(Order $order, OrderStatus $newStatus): Order
    {
        // Lógica de negocio aquí
        // Validaciones, transiciones, etc.
        
        return $order;
    }
}
```

### Casos de Uso Ideales

#### ✅ Usar Actions para:

1. **Operaciones de un solo paso:**
   ```php
   CreateProductAction::run($data);
   AddToWishlistAction::run($customer, $product);
   SetDefaultAddressAction::run($customer, $address);
   ```

2. **Validaciones y transformaciones:**
   ```php
   ValidateOrderAction::run($order);
   CalculateShippingAction::run($address, $items);
   ```

3. **Transiciones de estado:**
   ```php
   ChangeOrderStatusAction::run($order, OrderStatus::PAID);
   ```

4. **Operaciones CRUD complejas:**
   ```php
   CreateProductVariantAction::run($product, $data, $attributes);
   ```

### Ejemplos en el Proyecto

```php
// ✅ Crear una orden
$order = CreateOrderAction::run($customer, $cartItems, $shippingData);

// ✅ Cambiar estado de orden
$order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);

// ✅ Agregar a wishlist
AddToWishlistAction::run($customer, $product);

// ✅ Establecer dirección predeterminada
SetDefaultAddressAction::run($customer, $address);
```

### Ventajas

1. **Testabilidad**: Fácil de testear de forma aislada
2. **Mantenibilidad**: Código organizado y fácil de entender
3. **Reutilización**: Pueden ser llamadas desde controladores, jobs, comandos, etc.
4. **Transacciones**: Manejo seguro de operaciones complejas
5. **Trazabilidad**: Fácil de rastrear qué Action ejecutó qué operación

---

## 🏗️ Services (Cuándo Usarlos)

### ¿Qué son los Services?

Los **Services** son clases que agrupan **múltiples operaciones relacionadas** que comparten estado, configuración o conexiones externas. Son útiles cuando necesitas mantener contexto o conexiones persistentes.

### Características

- ✅ **Múltiples operaciones**: Agrupan funcionalidad relacionada
- ✅ **Con estado**: Pueden mantener configuración o conexiones
- ✅ **Conexiones externas**: Manejan SDKs, APIs, servicios externos
- ✅ **Configuración persistente**: Mantienen configuración entre llamadas

### Estructura

```php
<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $client;
    
    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }
    
    public function createPaymentIntent(float $amount, string $currency = 'mxn')
    {
        return $this->client->paymentIntents->create([
            'amount' => $amount * 100, // Stripe usa centavos
            'currency' => $currency,
        ]);
    }
    
    public function refundPayment(string $paymentIntentId)
    {
        return $this->client->refunds->create([
            'payment_intent' => $paymentIntentId,
        ]);
    }
    
    public function getCustomer(string $customerId)
    {
        return $this->client->customers->retrieve($customerId);
    }
}
```

### Casos de Uso Ideales

#### ✅ Usar Services para:

1. **Integraciones con APIs externas:**
   ```php
   $stripeService = new StripeService();
   $paymentIntent = $stripeService->createPaymentIntent(100.00);
   ```

2. **Conexiones con servicios externos:**
   ```php
   $emailService = new EmailService();
   $emailService->sendOrderConfirmation($order);
   ```

3. **SDKs que requieren configuración:**
   ```php
   $shippingService = new ShippingService();
   $shippingService->calculateRate($address, $weight);
   ```

4. **Agrupación de operaciones relacionadas:**
   ```php
   $inventoryService = new InventoryService();
   $inventoryService->reserveStock($product, $quantity);
   $inventoryService->releaseStock($product, $quantity);
   ```

### Ejemplos de Services Comunes

#### PaymentService

```php
<?php

namespace App\Services;

use Stripe\StripeClient;
use App\Domains\Sales\Models\Order;

class PaymentService
{
    protected StripeClient $stripe;
    
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }
    
    public function processPayment(Order $order): array
    {
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $order->total * 100,
            'currency' => 'mxn',
            'metadata' => ['order_id' => $order->id],
        ]);
        
        return [
            'client_secret' => $paymentIntent->client_secret,
            'payment_intent_id' => $paymentIntent->id,
        ];
    }
    
    public function confirmPayment(string $paymentIntentId): bool
    {
        $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
        return $paymentIntent->status === 'succeeded';
    }
    
    public function refund(Order $order): bool
    {
        // Lógica de reembolso
        return true;
    }
}
```

#### EmailService

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Domains\Sales\Models\Order;

class EmailService
{
    public function sendOrderConfirmation(Order $order): void
    {
        Mail::to($order->customer->email)->send(
            new OrderConfirmationMail($order)
        );
    }
    
    public function sendShippingNotification(Order $order): void
    {
        Mail::to($order->customer->email)->send(
            new ShippingNotificationMail($order)
        );
    }
    
    public function sendLowStockAlert($product): void
    {
        Mail::to(config('mail.admin'))->send(
            new LowStockAlertMail($product)
        );
    }
}
```

---

## 🔄 Cuándo Usar Cada Uno

### Decision Tree

```
¿Necesitas mantener estado o configuración?
├─ SÍ → ¿Es una integración con API/SDK externo?
│   ├─ SÍ → Usa Service
│   └─ NO → Considera Service si agrupa múltiples operaciones relacionadas
│
└─ NO → ¿Es una operación de un solo propósito?
    ├─ SÍ → Usa Action
    └─ NO → Divide en múltiples Actions
```

### Comparación Rápida

| Aspecto | Action | Service |
|---------|--------|---------|
| **Propósito** | Operación única | Múltiples operaciones relacionadas |
| **Estado** | Sin estado | Puede mantener estado |
| **Conexiones** | No mantiene conexiones | Mantiene conexiones (APIs, SDKs) |
| **Testabilidad** | Muy fácil | Requiere mocks para servicios externos |
| **Reutilización** | Alta | Media-Alta |
| **Complejidad** | Baja | Media-Alta |

---

## 📝 Mejores Prácticas

### Para Actions

1. **Un propósito, una Action:**
   ```php
   // ✅ CORRECTO
   CreateOrderAction::run($customer, $items);
   
   // ❌ INCORRECTO (demasiado complejo)
   ProcessOrderAction::run($customer, $items, $payment, $shipping);
   ```

2. **Usa transacciones para operaciones complejas:**
   ```php
   public function execute(...): Order
   {
       return DB::transaction(function () {
           // Múltiples operaciones relacionadas
       });
   }
   ```

3. **Valida inputs al inicio:**
   ```php
   public function execute(Order $order, OrderStatus $newStatus): Order
   {
       if (!$order->canTransitionTo($newStatus)) {
           throw new InvalidArgumentException('Transición inválida');
       }
       
       // Lógica de negocio
   }
   ```

4. **Retorna el modelo modificado:**
   ```php
   public function execute(...): Model
   {
       // Modificar modelo
       $model->save();
       
       return $model->fresh(); // Retorna con relaciones cargadas
   }
   ```

### Para Services

1. **Inyecta dependencias en el constructor:**
   ```php
   public function __construct(
       protected StripeClient $stripe,
       protected EmailService $email
   ) {}
   ```

2. **Usa interfaces para servicios externos:**
   ```php
   interface PaymentGatewayInterface
   {
       public function processPayment(float $amount): array;
   }
   
   class StripeService implements PaymentGatewayInterface
   {
       // Implementación
   }
   ```

3. **Maneja errores de servicios externos:**
   ```php
   public function processPayment(float $amount): array
   {
       try {
           return $this->stripe->paymentIntents->create([...]);
       } catch (\Stripe\Exception\ApiErrorException $e) {
           logger()->error('Stripe error', ['error' => $e->getMessage()]);
           throw new PaymentException('Error procesando pago');
       }
   }
   ```

4. **Usa cache para configuraciones costosas:**
   ```php
   public function getShippingRates($address): array
   {
       return Cache::remember("shipping_rates_{$address}", 3600, function () use ($address) {
           return $this->calculateRates($address);
       });
   }
   ```

---

## 🎯 Patrón Híbrido: Action + Service

En algunos casos, puedes combinar ambos:

```php
// Service maneja la conexión externa
class StripeService
{
    public function createPaymentIntent(float $amount): array
    {
        // Lógica de Stripe
    }
}

// Action orquesta la lógica de negocio
class ProcessPaymentAction extends Action
{
    public function __construct(
        protected StripeService $stripeService
    ) {}
    
    public function execute(Order $order): Order
    {
        // 1. Validar orden
        // 2. Usar service para crear payment intent
        $payment = $this->stripeService->createPaymentIntent($order->total);
        
        // 3. Actualizar orden con payment intent
        $order->payment_intent_id = $payment['id'];
        $order->save();
        
        // 4. Cambiar estado
        ChangeOrderStatusAction::run($order, OrderStatus::PAID);
        
        return $order;
    }
}
```

---

## 📊 Resumen

### Usa Actions cuando:
- ✅ Necesitas una operación de un solo propósito
- ✅ No necesitas mantener estado
- ✅ Quieres código fácil de testear
- ✅ La lógica es independiente y reutilizable

### Usa Services cuando:
- ✅ Necesitas integrar con APIs/SDKs externos
- ✅ Necesitas mantener conexiones o configuración
- ✅ Agrupas múltiples operaciones relacionadas
- ✅ Necesitas compartir estado entre operaciones

---

## 🚀 En Este Proyecto

### Estructura Actual (Actions)

```
app/Domains/
├── Catalog/Actions/
│   ├── CreateProductAction.php
│   └── CreateProductVariantAction.php
├── Sales/Actions/
│   ├── CreateOrderAction.php
│   └── ChangeOrderStatusAction.php
└── Customers/Actions/
    ├── AddToWishlistAction.php
    ├── RemoveFromWishlistAction.php
    └── SetDefaultAddressAction.php
```

### Cuándo Agregar Services

Agrega Services cuando necesites:

1. **Integración de pagos:**
   ```
   app/Services/PaymentService.php
   ```

2. **Envío de emails:**
   ```
   app/Services/EmailService.php
   ```

3. **Cálculo de envíos:**
   ```
   app/Services/ShippingService.php
   ```

4. **Integración con inventario:**
   ```
   app/Services/InventoryService.php
   ```

---

✅ **Mantén el enfoque actual de Actions para la lógica de negocio. Agrega Services solo cuando necesites integraciones externas o agrupar operaciones relacionadas con estado compartido.**


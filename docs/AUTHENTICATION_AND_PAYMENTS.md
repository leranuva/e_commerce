# Autenticación Multi-Auth y Integración de Pagos

## ✅ Implementación Completada

### 1. Autenticación Multi-Auth para Clientes

#### Configuración

**Guard 'customer' configurado en `config/auth.php`:**

```php
'guards' => [
    'customer' => [
        'driver' => 'session',
        'provider' => 'customers',
    ],
],

'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Domains\Customers\Models\Customer::class,
    ],
],
```

#### Controlador de Autenticación

**Ubicación:** `app/Http/Controllers/Auth/CustomerAuthController.php`

**Métodos:**
- `showLoginForm()` - Mostrar formulario de login
- `login()` - Procesar login
- `showRegisterForm()` - Mostrar formulario de registro
- `register()` - Procesar registro
- `logout()` - Cerrar sesión

#### Rutas

```php
Route::get('/customer/login', [CustomerAuthController::class, 'showLoginForm']);
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::get('/customer/register', [CustomerAuthController::class, 'showRegisterForm']);
Route::post('/customer/register', [CustomerAuthController::class, 'register']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);
```

### 2. Guest Checkout

#### Características

- ✅ **Carrito sin login**: Los usuarios pueden agregar productos sin registrarse
- ✅ **Login opcional**: Solo se requiere al final del checkout
- ✅ **Crear cuenta opcional**: Opción de crear cuenta durante el checkout
- ✅ **Cliente temporal**: Se crea cliente temporal si no se registra

#### Flujo

```
1. Usuario agrega productos al carrito (sin login)
   ↓
2. Usuario va al checkout
   ↓
3. Usuario completa datos de contacto y envío
   ↓
4. Usuario puede optar por crear cuenta (opcional)
   ↓
5. Usuario procede al pago
   ↓
6. Si no está autenticado, se crea cliente temporal
   ↓
7. Se procesa el pago y se crea la orden
```

### 3. Integración con Stripe

#### PaymentService

**Ubicación:** `app/Services/PaymentService.php`

**Características:**
- ✅ Creación de PaymentIntents
- ✅ Confirmación de pagos
- ✅ Manejo de webhooks
- ✅ Manejo de errores

**Métodos:**
```php
// Crear PaymentIntent
$paymentService->createPaymentIntent($amount, 'mxn', $metadata);

// Confirmar pago
$paymentService->confirmPayment($paymentIntentId);

// Obtener estado
$paymentService->getPaymentStatus($paymentIntentId);

// Procesar webhook
$paymentService->handleWebhook($payload);
```

#### Configuración

**Agregar a `.env`:**
```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

**Instalar Stripe PHP SDK:**
```bash
composer require stripe/stripe-php
```

#### Integración en Checkout

El componente `Checkout` ahora:
1. Crea PaymentIntent cuando el usuario procede al pago
2. Muestra formulario de Stripe Elements
3. Confirma el pago antes de crear la orden
4. Solo crea la orden si el pago es exitoso

### 4. Optimizaciones de Rendimiento

#### Lazy Loading

**Componentes con lazy loading:**
- `AddToCartButton` en lista de productos
- `CartItems` (componente separado para mejor rendimiento)

**Uso:**
```blade
<livewire:add-to-cart-button :product="$product" lazy />
<livewire:cart-items lazy />
```

**Beneficios:**
- ✅ Mejora LCP (Largest Contentful Paint)
- ✅ Carga diferida de componentes pesados
- ✅ Mejor experiencia inicial

#### Entangled State (Alpine.js)

**Selector de variantes con estado reactivo:**

```blade
<div x-data="{ selectedVariant: null }">
    <!-- Variantes con selección instantánea -->
    <div @click="selectedVariant = {{ $variant->id }}">
        <!-- UI reactiva sin esperar al servidor -->
    </div>
</div>
```

**Beneficios:**
- ✅ Cambios de UI instantáneos
- ✅ Sin esperar respuesta del servidor
- ✅ Mejor UX para selectores

#### Lazy Loading de Imágenes

```blade
<img src="{{ $image }}" loading="lazy" />
```

## 📋 Flujo Completo de Checkout

### Paso 1: Información de Cliente y Envío

- Datos de contacto (nombre, email, teléfono)
- Opción de crear cuenta
- Información de envío
- Resumen del pedido

### Paso 2: Pago con Stripe

- Formulario de Stripe Elements
- Confirmación de pago
- Creación de orden solo si el pago es exitoso

## 🔧 Configuración Requerida

### 1. Instalar Stripe PHP SDK

```bash
composer require stripe/stripe-php
```

### 2. Configurar Variables de Entorno

```env
STRIPE_KEY=pk_test_tu_clave_publica
STRIPE_SECRET=sk_test_tu_clave_secreta
STRIPE_WEBHOOK_SECRET=whsec_tu_secreto_webhook
```

### 3. Configurar Webhook en Stripe Dashboard

1. Ir a Stripe Dashboard → Webhooks
2. Agregar endpoint: `https://tudominio.com/webhooks/stripe`
3. Seleccionar eventos:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
4. Copiar el secreto del webhook a `.env`

### 4. Crear Ruta de Webhook (Opcional)

```php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
```

## 🎯 Características Destacadas

### Guest Checkout

- ✅ No requiere registro previo
- ✅ Carrito funciona sin autenticación
- ✅ Opción de crear cuenta durante checkout
- ✅ Cliente temporal si no se registra

### Seguridad

- ✅ Validación de pagos antes de crear orden
- ✅ PaymentIntents seguros
- ✅ No se manejan datos sensibles en el servidor
- ✅ Webhooks para confirmación

### Rendimiento

- ✅ Lazy loading de componentes
- ✅ Estado reactivo con Alpine.js
- ✅ Lazy loading de imágenes
- ✅ Optimización de LCP

## 📝 Notas Importantes

### Guest Checkout

El sistema crea un cliente temporal si el usuario no se registra. Para mejorar esto:

1. Hacer `password` nullable en la migración de `customers`
2. O crear una tabla separada para clientes guest
3. O usar un sistema de "guest orders" sin cliente

### Stripe

- Usa claves de prueba para desarrollo
- Configura webhooks para producción
- Valida siempre los pagos antes de crear órdenes
- Maneja errores de pago apropiadamente

### Optimizaciones

- Lazy loading solo en componentes que no son críticos
- Alpine.js para interacciones instantáneas
- Lazy loading de imágenes para mejor LCP

---

✅ **Autenticación multi-auth, Guest Checkout y Stripe completamente implementados!**


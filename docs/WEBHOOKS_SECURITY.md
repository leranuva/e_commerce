# Seguridad de Webhooks y Prevención de Órdenes sin Pago

## 🔒 Implementación de Seguridad Crítica

### Problema Resuelto

**ANTES (Inseguro):**
- El cliente podía crear órdenes directamente
- No había garantía de que el pago fuera exitoso
- Vulnerable a manipulación del lado del cliente

**AHORA (Seguro):**
- Las órdenes se crean como PENDIENTES con `payment_intent_id`
- Solo el webhook de Stripe puede confirmar la orden
- Validación de firmas de webhook
- Imposible crear órdenes sin pago confirmado

## 🏗️ Arquitectura de Seguridad

### Flujo Seguro de Checkout

```
1. Usuario completa datos y procede al pago
   ↓
2. Se crea PaymentIntent en Stripe
   ↓
3. Se crea orden PENDIENTE con payment_intent_id
   ↓
4. Usuario completa pago en Stripe Elements
   ↓
5. Stripe procesa el pago
   ↓
6. Stripe envía webhook a nuestro servidor
   ↓
7. Webhook valida firma y confirma la orden
   ↓
8. Orden cambia de PENDING a PAID
```

### Puntos Críticos de Seguridad

1. **Validación de Firmas de Webhook**
   - Todos los webhooks se validan con `Stripe-Signature`
   - Imposible falsificar eventos
   - Logs de intentos de manipulación

2. **Órdenes Pendientes**
   - Se crean ANTES del pago con `payment_intent_id`
   - Estado inicial: `PENDING`
   - Solo el webhook puede cambiar a `PAID`

3. **No Confiar en el Cliente**
   - El cliente NUNCA puede confirmar órdenes directamente
   - Solo Stripe (vía webhook) puede confirmar pagos
   - Verificación doble: cliente + webhook

## 📋 Componentes Implementados

### 1. Migración: payment_intent_id

**Archivo:** `database/migrations/2025_12_28_000001_add_payment_intent_id_to_orders_table.php`

```php
$table->string('payment_intent_id')->nullable()->unique()->after('status');
$table->index('payment_intent_id');
```

**Características:**
- Campo único para evitar duplicados
- Índice para búsquedas rápidas
- Nullable para órdenes sin pago (futuro)

### 2. Modelo Order Actualizado

**Campo agregado:**
```php
protected $fillable = [
    // ...
    'payment_intent_id',
];
```

**Relación:**
- Una orden tiene un `payment_intent_id` único
- Permite buscar órdenes por PaymentIntent

### 3. CreateOrderAction Actualizado

**Nuevo parámetro:**
```php
public function execute(
    Customer $customer, 
    array $cartItems, 
    array $shippingData, 
    ?string $paymentIntentId = null
): Order
```

**Comportamiento:**
- Crea orden con `payment_intent_id` si se proporciona
- Estado inicial: `PENDING`
- El webhook confirmará la orden

### 4. StripeWebhookController

**Ubicación:** `app/Http/Controllers/StripeWebhookController.php`

**Características de Seguridad:**

#### Validación de Firmas

```php
$event = Webhook::constructEvent(
    $payload,
    $sigHeader,
    $webhookSecret
);
```

- Valida que el webhook viene de Stripe
- Rechaza eventos con firmas inválidas
- Logs de intentos de manipulación

#### Procesamiento de Eventos

**payment_intent.succeeded:**
- Busca orden por `payment_intent_id`
- Cambia estado de `PENDING` a `PAID`
- Solo aquí se confirma la orden

**payment_intent.payment_failed:**
- Cancela la orden pendiente
- Cambia estado a `CANCELLED`

**payment_intent.canceled:**
- Cancela la orden pendiente

### 5. Checkout Component Actualizado

**Nuevo Flujo:**

1. **proceedToPayment():**
   - Crea PaymentIntent en Stripe
   - Crea orden PENDIENTE con `payment_intent_id`
   - Muestra formulario de pago

2. **handlePaymentSuccess():**
   - Verifica que el pago fue exitoso
   - Busca orden pendiente
   - Redirige a página de procesamiento
   - Espera confirmación del webhook

### 6. Página de Procesamiento

**Nueva ruta:** `/checkout/processing/{order}`

**Funcionalidades:**
- Muestra orden pendiente
- Polling automático para verificar confirmación
- Redirige automáticamente cuando el webhook confirma

## 🔐 Medidas de Seguridad

### 1. Validación de Firmas

```php
try {
    $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
} catch (SignatureVerificationException $e) {
    // Rechazar webhook - posible ataque
    Log::warning('Stripe webhook signature verification failed');
    return response()->json(['error' => 'Invalid signature'], 400);
}
```

### 2. Exclusión de CSRF

```php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
```

**Razón:** Los webhooks de Stripe no pueden enviar tokens CSRF.

### 3. Logging de Eventos

```php
Log::info('Stripe webhook received', [
    'type' => $event->type,
    'payment_intent_id' => $paymentIntentId,
]);
```

**Beneficios:**
- Auditoría completa
- Debugging de problemas
- Detección de anomalías

### 4. Idempotencia

- Búsqueda por `payment_intent_id` único
- Evita procesar el mismo evento dos veces
- Verificación de estado antes de cambiar

## 📊 Flujo Completo de Seguridad

### Escenario 1: Pago Exitoso

```
1. Cliente completa checkout
   ↓
2. Se crea PaymentIntent: pi_xxx
   ↓
3. Se crea Orden #123 (PENDING, payment_intent_id: pi_xxx)
   ↓
4. Cliente paga en Stripe Elements
   ↓
5. Stripe procesa pago exitosamente
   ↓
6. Stripe envía webhook: payment_intent.succeeded
   ↓
7. Webhook valida firma ✓
   ↓
8. Webhook busca Orden #123 por payment_intent_id
   ↓
9. Webhook cambia estado: PENDING → PAID
   ↓
10. Cliente ve confirmación
```

### Escenario 2: Pago Fallido

```
1. Cliente completa checkout
   ↓
2. Se crea PaymentIntent: pi_xxx
   ↓
3. Se crea Orden #123 (PENDING, payment_intent_id: pi_xxx)
   ↓
4. Cliente intenta pagar pero falla
   ↓
5. Stripe envía webhook: payment_intent.payment_failed
   ↓
6. Webhook valida firma ✓
   ↓
7. Webhook busca Orden #123
   ↓
8. Webhook cambia estado: PENDING → CANCELLED
   ↓
9. Cliente ve error de pago
```

### Escenario 3: Intento de Manipulación

```
1. Atacante intenta enviar webhook falso
   ↓
2. Webhook llega sin firma válida
   ↓
3. Validación falla: SignatureVerificationException
   ↓
4. Webhook es rechazado (400)
   ↓
5. Evento se registra en logs
   ↓
6. Ninguna orden es afectada
```

## ⚙️ Configuración Requerida

### 1. Variables de Entorno

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...  # CRÍTICO para seguridad
```

### 2. Configurar Webhook en Stripe Dashboard

1. Ir a: https://dashboard.stripe.com/webhooks
2. Click en "Add endpoint"
3. URL: `https://tudominio.com/webhooks/stripe`
4. Seleccionar eventos:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payment_intent.canceled`
5. Copiar "Signing secret" a `.env`

### 3. Probar Webhook Localmente

**Opción 1: Stripe CLI**
```bash
stripe listen --forward-to localhost:8000/webhooks/stripe
```

**Opción 2: ngrok**
```bash
ngrok http 8000
# Usar URL de ngrok en Stripe Dashboard
```

## 🧪 Testing de Seguridad

### Test 1: Validación de Firmas

```php
// Enviar webhook sin firma válida
// Resultado esperado: 400 Bad Request
```

### Test 2: Orden sin Pago

```php
// Intentar crear orden sin payment_intent_id
// Resultado esperado: Orden se crea como PENDING
```

### Test 3: Webhook Falso

```php
// Enviar evento payment_intent.succeeded falso
// Resultado esperado: Rechazado por validación de firma
```

## 📝 Checklist de Seguridad

- [x] Validación de firmas de webhook implementada
- [x] Webhook excluido de CSRF
- [x] Órdenes se crean como PENDING
- [x] Solo webhook puede confirmar órdenes
- [x] Logging de eventos de seguridad
- [x] Manejo de errores robusto
- [x] Idempotencia en procesamiento
- [x] Campo payment_intent_id único

## 🚨 Mejores Prácticas

### 1. Nunca Confiar en el Cliente

```php
// ❌ INCORRECTO
if ($clientSaysPaymentSucceeded) {
    $order->status = 'paid';
}

// ✅ CORRECTO
if ($webhookConfirmsPayment) {
    $order->status = 'paid';
}
```

### 2. Siempre Validar Firmas

```php
// ❌ INCORRECTO
$event = json_decode($request->getContent());

// ✅ CORRECTO
$event = Webhook::constructEvent($payload, $sigHeader, $secret);
```

### 3. Logging de Seguridad

```php
// Registrar todos los eventos de webhook
Log::info('Webhook received', ['type' => $event->type]);
Log::warning('Invalid signature', ['ip' => $request->ip()]);
```

### 4. Manejo de Errores

```php
try {
    // Procesar webhook
} catch (SignatureVerificationException $e) {
    // Rechazar y loggear
    Log::warning('Invalid webhook signature');
    return response()->json(['error' => 'Invalid signature'], 400);
}
```

## 🔄 Flujo de Recuperación

### Si el Webhook Falla

1. **Reintento Automático:**
   - Stripe reintenta webhooks automáticamente
   - Hasta 3 intentos en 3 días

2. **Verificación Manual:**
   - Revisar órdenes pendientes en admin
   - Verificar estado en Stripe Dashboard
   - Reprocesar manualmente si es necesario

3. **Sincronización:**
   - Comando artisan para sincronizar órdenes pendientes
   - Verificar estado en Stripe y actualizar localmente

## 📊 Métricas de Seguridad

### Monitoreo Recomendado

- Número de webhooks recibidos
- Número de firmas inválidas rechazadas
- Tiempo promedio de confirmación de órdenes
- Órdenes pendientes sin confirmar (> 5 minutos)

---

✅ **Sistema de seguridad implementado. Las órdenes solo se confirman cuando Stripe valida el pago mediante webhook.**


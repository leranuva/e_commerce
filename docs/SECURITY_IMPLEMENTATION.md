# Implementación de Seguridad: Webhooks y Prevención de Órdenes sin Pago

## ✅ Implementación Completada

Se ha implementado un sistema de seguridad robusto que **garantiza que ninguna orden se confirme sin un pago verificado por Stripe**.

## 🔒 Principio de Seguridad

**NUNCA confiar en el cliente. Solo confiar en webhooks verificados de Stripe.**

### Antes (Inseguro) ❌

```php
// Cliente confirma pago
if ($clientSaysPaymentSucceeded) {
    $order = CreateOrderAction::run(...);
    $order->status = 'paid'; // PELIGROSO
}
```

### Ahora (Seguro) ✅

```php
// 1. Cliente crea orden PENDIENTE con payment_intent_id
$order = CreateOrderAction::run(..., $paymentIntentId);

// 2. Solo webhook de Stripe puede confirmar
// StripeWebhookController valida firma y confirma
if ($webhookValidated && $paymentSucceeded) {
    $order->status = 'paid'; // SEGURO
}
```

## 🏗️ Arquitectura de Seguridad

### Flujo Seguro Completo

```
┌─────────────────────────────────────────────────────────────┐
│ 1. CLIENTE: Completa checkout                                │
│    - Datos de envío                                          │
│    - Información de contacto                                 │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. SERVIDOR: Crea PaymentIntent en Stripe                   │
│    - Monto: $total                                           │
│    - Currency: MXN                                            │
│    - Metadata: customer info                                 │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. SERVIDOR: Crea Orden PENDIENTE                           │
│    - Status: PENDING                                         │
│    - payment_intent_id: pi_xxx (vinculado)                  │
│    - NO se confirma aún                                      │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. CLIENTE: Completa pago en Stripe Elements                 │
│    - Tarjeta de crédito                                      │
│    - Stripe procesa el pago                                  │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. STRIPE: Procesa pago                                     │
│    - Si exitoso → Envía webhook                              │
│    - Si falla → Envía webhook de error                      │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. WEBHOOK: Llega a nuestro servidor                        │
│    - URL: /webhooks/stripe                                   │
│    - Headers: Stripe-Signature                               │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. SERVIDOR: Valida firma del webhook                        │
│    - Webhook::constructEvent()                               │
│    - Verifica que viene de Stripe                           │
│    - Rechaza si firma inválida                               │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. SERVIDOR: Procesa evento                                  │
│    - Busca orden por payment_intent_id                      │
│    - Si payment_intent.succeeded → PENDING → PAID           │
│    - Si payment_intent.failed → PENDING → CANCELLED         │
└────────────────────┬────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────┐
│ 9. CLIENTE: Ve confirmación                                  │
│    - Página de procesamiento (polling)                       │
│    - Redirige a éxito cuando webhook confirma               │
└─────────────────────────────────────────────────────────────┘
```

## 📋 Componentes de Seguridad

### 1. Migración: payment_intent_id

**Archivo:** `database/migrations/2025_12_28_000001_add_payment_intent_id_to_orders_table.php`

```php
$table->string('payment_intent_id')->nullable()->unique();
$table->index('payment_intent_id');
```

**Características:**
- Campo único para evitar duplicados
- Índice para búsquedas rápidas
- Permite vincular órdenes con PaymentIntents

### 2. Modelo Order

**Campo agregado:**
```php
'payment_intent_id' => 'string|nullable|unique'
```

**Métodos:**
- Búsqueda por `payment_intent_id`
- Relación con PaymentIntent de Stripe

### 3. CreateOrderAction

**Actualizado para aceptar payment_intent_id:**
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
- El webhook confirmará cuando el pago sea exitoso

### 4. StripeWebhookController

**Ubicación:** `app/Http/Controllers/StripeWebhookController.php`

**Características de Seguridad:**

#### Validación de Firmas

```php
$event = Webhook::constructEvent(
    $payload,
    $request->header('Stripe-Signature'),
    config('services.stripe.webhook_secret')
);
```

**Protecciones:**
- ✅ Valida que el webhook viene de Stripe
- ✅ Rechaza eventos con firmas inválidas
- ✅ Logs de intentos de manipulación
- ✅ Retorna 400 si la firma es inválida

#### Procesamiento Seguro

**payment_intent.succeeded:**
```php
// Buscar orden por payment_intent_id
$order = Order::where('payment_intent_id', $paymentIntentId)->first();

// Solo cambiar estado si existe y está pendiente
if ($order && $order->status === OrderStatus::PENDING) {
    ChangeOrderStatusAction::run($order, OrderStatus::PAID);
}
```

**payment_intent.payment_failed:**
```php
// Cancelar orden pendiente
if ($order && $order->canTransitionTo(OrderStatus::CANCELLED)) {
    ChangeOrderStatusAction::run($order, OrderStatus::CANCELLED);
}
```

### 5. Checkout Component

**Flujo Actualizado:**

1. **proceedToPayment():**
   ```php
   // 1. Crear PaymentIntent
   $paymentData = $paymentService->createPaymentIntent(...);
   
   // 2. Crear orden PENDIENTE con payment_intent_id
   $order = CreateOrderAction::run(..., $paymentIntentId);
   
   // 3. Mostrar formulario de pago
   ```

2. **handlePaymentSuccess():**
   ```php
   // Verificar que el pago fue exitoso
   // Buscar orden pendiente
   // Redirigir a página de procesamiento
   // Esperar confirmación del webhook
   ```

### 6. Página de Procesamiento

**Nueva ruta:** `/checkout/processing/{order}`

**Funcionalidades:**
- Muestra orden pendiente
- Polling automático cada segundo
- Redirige cuando el webhook confirma
- Timeout después de 30 segundos

## 🔐 Medidas de Seguridad Implementadas

### 1. Validación de Firmas de Webhook

```php
try {
    $event = Webhook::constructEvent($payload, $sigHeader, $secret);
} catch (SignatureVerificationException $e) {
    Log::warning('Invalid webhook signature', ['ip' => $request->ip()]);
    return response()->json(['error' => 'Invalid signature'], 400);
}
```

**Protección:**
- Imposible falsificar webhooks
- Solo Stripe puede enviar eventos válidos
- Logs de intentos de manipulación

### 2. Exclusión de CSRF

```php
Route::post('/webhooks/stripe', ...)
    ->withoutMiddleware([ValidateCsrfToken::class]);
```

**Razón:** Los webhooks de Stripe no pueden enviar tokens CSRF.

**Compensación:** Validación de firmas reemplaza CSRF.

### 3. Estado de Órdenes

- **PENDING**: Orden creada, esperando confirmación de pago
- **PAID**: Orden confirmada por webhook (pago exitoso)
- **CANCELLED**: Orden cancelada (pago fallido o cancelado)

**Transiciones:**
- Solo webhook puede cambiar PENDING → PAID
- Solo webhook puede cambiar PENDING → CANCELLED
- Cliente NUNCA puede cambiar estado

### 4. Idempotencia

```php
// Buscar orden existente por payment_intent_id
$order = Order::where('payment_intent_id', $paymentIntentId)->first();

// Solo procesar si no se ha procesado antes
if ($order && $order->status === OrderStatus::PENDING) {
    // Procesar
}
```

**Beneficios:**
- Evita procesar el mismo evento dos veces
- Maneja reintentos de Stripe correctamente
- Previene duplicación de confirmaciones

### 5. Logging de Seguridad

```php
Log::info('Stripe webhook received', [
    'type' => $event->type,
    'payment_intent_id' => $paymentIntentId,
]);

Log::warning('Invalid webhook signature', [
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

**Beneficios:**
- Auditoría completa de eventos
- Detección de intentos de manipulación
- Debugging de problemas

## 🧪 Escenarios de Seguridad

### Escenario 1: Pago Exitoso Normal

```
1. Cliente completa checkout
2. Orden #123 creada (PENDING, payment_intent_id: pi_abc)
3. Cliente paga exitosamente
4. Stripe envía webhook: payment_intent.succeeded
5. Webhook valida firma ✓
6. Webhook busca Orden #123 por pi_abc
7. Webhook cambia: PENDING → PAID
8. Cliente ve confirmación
```

### Escenario 2: Pago Fallido

```
1. Cliente completa checkout
2. Orden #123 creada (PENDING, payment_intent_id: pi_abc)
3. Cliente intenta pagar pero falla
4. Stripe envía webhook: payment_intent.payment_failed
5. Webhook valida firma ✓
6. Webhook busca Orden #123
7. Webhook cambia: PENDING → CANCELLED
8. Cliente ve error de pago
```

### Escenario 3: Intento de Manipulación

```
1. Atacante intenta enviar webhook falso
2. Webhook llega sin firma válida
3. Validación falla: SignatureVerificationException
4. Webhook es rechazado (400 Bad Request)
5. Evento se registra en logs
6. Ninguna orden es afectada
```

### Escenario 4: Webhook Duplicado

```
1. Stripe envía webhook: payment_intent.succeeded
2. Webhook procesa: PENDING → PAID
3. Stripe reenvía el mismo webhook (reintento)
4. Webhook busca orden por payment_intent_id
5. Orden ya está en estado PAID
6. Webhook no procesa (idempotencia)
7. Retorna 200 OK (ya procesado)
```

## ⚙️ Configuración

### 1. Variables de Entorno

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...  # CRÍTICO
```

### 2. Configurar Webhook en Stripe

1. Dashboard: https://dashboard.stripe.com/webhooks
2. Add endpoint: `https://tudominio.com/webhooks/stripe`
3. Eventos:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payment_intent.canceled`
4. Copiar "Signing secret" a `.env`

### 3. Testing Local

**Stripe CLI:**
```bash
stripe listen --forward-to localhost:8000/webhooks/stripe
```

**ngrok:**
```bash
ngrok http 8000
# Usar URL de ngrok en Stripe Dashboard
```

## 📊 Monitoreo y Alertas

### Métricas Recomendadas

1. **Webhooks Recibidos:**
   - Total de webhooks por día
   - Tipos de eventos más comunes

2. **Firmas Inválidas:**
   - Número de rechazos por día
   - IPs de origen sospechosas

3. **Órdenes Pendientes:**
   - Órdenes pendientes > 5 minutos
   - Tasa de confirmación exitosa

4. **Tiempo de Confirmación:**
   - Tiempo promedio desde pago hasta confirmación
   - Órdenes que nunca se confirmaron

### Alertas

- Webhooks rechazados > 10 por hora
- Órdenes pendientes > 30 minutos
- Tasa de confirmación < 95%

## 🔄 Recuperación de Errores

### Si el Webhook Falla

1. **Reintento Automático:**
   - Stripe reintenta automáticamente
   - Hasta 3 intentos en 3 días

2. **Verificación Manual:**
   - Revisar órdenes pendientes en admin
   - Verificar estado en Stripe Dashboard
   - Comando artisan para sincronizar

3. **Comando de Sincronización:**
   ```php
   php artisan orders:sync-payments
   ```
   (Pendiente de implementar)

## ✅ Checklist de Seguridad

- [x] Validación de firmas de webhook implementada
- [x] Webhook excluido de CSRF
- [x] Órdenes se crean como PENDING
- [x] Solo webhook puede confirmar órdenes
- [x] Campo payment_intent_id único
- [x] Logging de eventos de seguridad
- [x] Manejo de errores robusto
- [x] Idempotencia en procesamiento
- [x] Página de procesamiento con polling
- [x] Prevención de manipulación

## 🚨 Mejores Prácticas Aplicadas

1. ✅ **Nunca confiar en el cliente**
2. ✅ **Siempre validar firmas de webhook**
3. ✅ **Logging de eventos de seguridad**
4. ✅ **Manejo robusto de errores**
5. ✅ **Idempotencia en procesamiento**
6. ✅ **Estado de órdenes controlado**

---

✅ **Sistema de seguridad completo. Las órdenes solo se confirman cuando Stripe valida el pago mediante webhook verificado.**


# Implementación de los Dominios - Lógica de Negocio

## 📋 Índice

1. [Catalog Domain - Variantes de Producto](#catalog-domain---variantes-de-producto)
2. [Sales Domain - State Machine para Órdenes](#sales-domain---state-machine-para-órdenes)
3. [Customers Domain - Wishlist y Direcciones](#customers-domain---wishlist-y-direcciones)
4. [Action Classes Implementadas](#action-classes-implementadas)
5. [Componentes Livewire](#componentes-livewire)
6. [Estructura de Base de Datos](#estructura-de-base-de-datos)
7. [Ejemplos de Uso](#ejemplos-de-uso)

---

## 📦 Catalog Domain - Variantes de Producto

### Descripción

Sistema completo de variantes de productos que permite gestionar diferentes combinaciones de atributos (talla, color, material) para un mismo producto base. Cada variante puede tener su propio precio, stock e imagen.

### Migraciones

#### `product_variants` Table

```php
Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->string('sku')->unique();
    $table->decimal('price', 10, 2)->nullable(); // Precio específico o null para usar precio del producto
    $table->integer('stock')->default(0);
    $table->string('image_path')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### `variant_attributes` Table

```php
Schema::create('variant_attributes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variant_id')->constrained('product_variants')->onDelete('cascade');
    $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
    $table->string('value'); // Ej: "XL", "Rojo", "Algodón"
    $table->timestamps();
    
    $table->unique(['variant_id', 'attribute_id']);
});
```

### Modelos

#### ProductVariant

**Ubicación:** `app/Domains/Catalog/Models/ProductVariant.php`

**Características:**
- Implementa `HasMedia` para gestión de imágenes
- Relación con Product (belongsTo)
- Relación con Attributes (belongsToMany)
- Métodos helper:
  - `getEffectivePriceAttribute()` - Obtiene precio de variante o producto
  - `getFullSkuAttribute()` - SKU completo (producto + variante)

**Relaciones:**
```php
// Producto padre
$variant->product

// Atributos de la variante
$variant->attributes // Con pivot 'value'
```

#### Product (Actualizado)

**Nueva Relación:**
```php
public function variants(): HasMany
{
    return $this->hasMany(ProductVariant::class);
}
```

### Action Class: CreateProductVariantAction

**Ubicación:** `app/Domains/Catalog/Actions/CreateProductVariantAction.php`

**Funcionalidad:**
- Crea variantes de productos con sus atributos
- Genera SKU único automáticamente si no se proporciona
- Asocia atributos (talla, color, material) a la variante
- Maneja precios específicos por variante

**Uso:**
```php
use App\Domains\Catalog\Actions\CreateProductVariantAction;

$variant = CreateProductVariantAction::run(
    $product,
    [
        'sku' => 'PROD-001-XL-RED', // Opcional
        'price' => 29.99, // Opcional, null usa precio del producto
        'stock' => 50,
    ],
    [
        'size' => 'XL',
        'color' => 'Rojo',
        'material' => 'Algodón',
    ]
);
```

**Generación de SKU:**
- Formato: `{PRODUCT_SKU}-{ATTR1}-{ATTR2}-...`
- Ejemplo: `CAM-001-XLREDALG`
- Asegura unicidad automáticamente

---

## 💰 Sales Domain - State Machine para Órdenes

### Descripción

Sistema de State Machine que controla las transiciones de estado de las órdenes, previniendo cambios inválidos y garantizando un flujo correcto del proceso de venta.

### Estados y Transiciones

```
┌──────────┐
│ Pendiente│
└────┬─────┘
     │
     ├──────────────┐
     │              │
     ▼              ▼
┌─────────┐    ┌──────────┐
│ Pagado  │    │Cancelado │
└────┬────┘    └──────────┘
     │
     ├──────────────┐
     │              │
     ▼              ▼
┌─────────┐    ┌──────────┐
│ Enviado │    │Cancelado │
└────┬────┘    └──────────┘
     │
     ▼
┌───────────┐
│ Entregado │ (Estado Final)
└───────────┘
```

### OrderStatus State Machine

**Ubicación:** `app/Domains/Sales/States/OrderStatus.php`

**Estados:**
- `pending` - Pendiente de pago
- `paid` - Pagado
- `shipped` - Enviado
- `delivered` - Entregado (estado final)
- `cancelled` - Cancelado (estado final)

**Transiciones Permitidas:**
- `pending` → `paid`, `cancelled`
- `paid` → `shipped`, `cancelled`
- `shipped` → `delivered`
- `delivered` → (ninguna, estado final)
- `cancelled` → (ninguna, estado final)

**Métodos Principales:**
```php
// Obtener todas las transiciones permitidas desde un estado
OrderStatus::getTransitions('pending'); // ['paid', 'cancelled']

// Verificar si una transición es válida
OrderStatus::canTransition('pending', 'paid'); // true
OrderStatus::canTransition('pending', 'delivered'); // false

// Obtener label en español
OrderStatus::getLabel('pending'); // 'Pendiente'

// Verificar si es estado final
OrderStatus::isFinal('delivered'); // true
```

### Modelo Order (Actualizado)

**Nuevos Métodos:**
```php
// Cambiar estado usando State Machine
$order->transitionTo(OrderStatus::PAID);

// Verificar si puede cambiar a un estado
if ($order->canTransitionTo(OrderStatus::SHIPPED)) {
    // ...
}

// Obtener label del estado actual
$order->status_label; // 'Pendiente'

// Verificar si está en estado final
$order->isFinal(); // false
```

**Validación Automática:**
- Lanza `InvalidArgumentException` si la transición no es válida
- Mensaje descriptivo con transiciones permitidas

### Action Class: ChangeOrderStatusAction

**Ubicación:** `app/Domains/Sales/Actions/ChangeOrderStatusAction.php`

**Funcionalidad:**
- Valida que el estado sea válido
- Valida que la transición sea permitida
- Cambia el estado de forma segura
- Listo para disparar eventos/notificaciones

**Uso:**
```php
use App\Domains\Sales\Actions\ChangeOrderStatusAction;
use App\Domains\Sales\States\OrderStatus;

// Cambiar estado de forma segura
$order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);

// Esto lanzará excepción si la transición no es válida
try {
    $order = ChangeOrderStatusAction::run($order, OrderStatus::DELIVERED);
} catch (InvalidArgumentException $e) {
    // Manejar error: "No se puede cambiar de 'paid' a 'delivered'"
}
```

### Migración Actualizada

**Cambio en `orders` table:**
```php
// Antes: 'processing'
// Ahora: 'paid'
$table->enum('status', ['pending', 'paid', 'shipped', 'delivered', 'cancelled'])
    ->default('pending');
```

---

## 👥 Customers Domain - Wishlist y Direcciones

### Wishlist

#### Descripción

Sistema de lista de deseos que permite a los clientes guardar productos (con o sin variantes específicas) para comprar más tarde. Implementado con componentes Livewire para una experiencia reactiva.

#### Migración: `wishlists`

```php
Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
    $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
    $table->foreignId('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
    $table->timestamps();
    
    // Un cliente no puede tener el mismo producto/variante dos veces
    $table->unique(['customer_id', 'product_id', 'variant_id']);
});
```

#### Modelo: Wishlist

**Ubicación:** `app/Domains/Customers/Models/Wishlist.php`

**Relaciones:**
```php
$wishlist->customer  // Cliente propietario
$wishlist->product    // Producto guardado
$wishlist->variant   // Variante específica (opcional)
```

#### Action Classes

**AddToWishlistAction**
- Verifica si ya existe en wishlist
- Crea nuevo item si no existe
- Retorna el item existente si ya está en la lista

**RemoveFromWishlistAction**
- Busca y elimina el item de wishlist
- Retorna `true` si se eliminó, `false` si no existía

**Uso:**
```php
use App\Domains\Customers\Actions\AddToWishlistAction;
use App\Domains\Customers\Actions\RemoveFromWishlistAction;

// Agregar producto sin variante
AddToWishlistAction::run($customer, $product);

// Agregar producto con variante específica
AddToWishlistAction::run($customer, $product, $variant);

// Remover de wishlist
RemoveFromWishlistAction::run($customer, $product, $variant);
```

#### Componentes Livewire

**WishlistButton**
- Botón reactivo para agregar/remover de wishlist
- Cambia de estado sin recargar página
- Muestra feedback visual inmediato
- Requiere autenticación de cliente

**WishlistPage**
- Página completa de wishlist con paginación
- Muestra productos guardados con imágenes
- Botón para remover de lista
- Enlace a página de producto

### Direcciones Predeterminadas

#### Descripción

Sistema que permite a los clientes tener múltiples direcciones, pero solo una puede ser predeterminada. Implementado con componente Livewire para gestión sin recargar página.

#### Migración: `customer_addresses`

Ya incluye el campo `is_default`:
```php
$table->boolean('is_default')->default(false);
```

#### Action Class: SetDefaultAddressAction

**Ubicación:** `app/Domains/Customers/Actions/SetDefaultAddressAction.php`

**Funcionalidad:**
- Valida que la dirección pertenezca al cliente
- Usa transacción para garantizar consistencia
- Quita el flag de todas las direcciones del cliente
- Establece la nueva dirección como predeterminada

**Uso:**
```php
use App\Domains\Customers\Actions\SetDefaultAddressAction;

SetDefaultAddressAction::run($customer, $address);
// Automáticamente quita is_default de otras direcciones
```

#### Modelo Customer (Actualizado)

**Nuevos Métodos:**
```php
// Relación con wishlist
$customer->wishlist()

// Obtener dirección predeterminada
$customer->defaultAddress() // Retorna CustomerAddress o null
```

#### Componente Livewire: AddressManager

**Funcionalidades:**
- ✅ Agregar nuevas direcciones
- ✅ Editar direcciones existentes
- ✅ Establecer dirección predeterminada
- ✅ Eliminar direcciones
- ✅ Validaciones en tiempo real
- ✅ Todo sin recargar la página

**Características:**
- Formulario reactivo con validación
- Lista de direcciones con badges
- Botones de acción por dirección
- Confirmación antes de eliminar
- Feedback visual inmediato

---

## 🎯 Action Classes Implementadas

### Resumen Completo

| Action Class | Dominio | Funcionalidad |
|-------------|--------|---------------|
| `CreateProductAction` | Catalog | Crea productos con imágenes |
| `CreateProductVariantAction` | Catalog | Crea variantes con atributos |
| `CreateOrderAction` | Sales | Crea órdenes con cálculos |
| `ChangeOrderStatusAction` | Sales | Cambia estado usando State Machine |
| `UpdateCustomerProfileAction` | Customers | Actualiza perfil de cliente |
| `AddToWishlistAction` | Customers | Agrega a wishlist |
| `RemoveFromWishlistAction` | Customers | Remueve de wishlist |
| `SetDefaultAddressAction` | Customers | Establece dirección predeterminada |

### Patrón de Uso

Todas las Action Classes siguen el mismo patrón:

```php
// Método estático directo
$result = ActionClass::run($param1, $param2, ...);

// O instanciar y ejecutar
$action = new ActionClass();
$result = $action->execute($param1, $param2, ...);
```

### Ventajas

1. **Separación de Responsabilidades**: Lógica de negocio fuera de controladores
2. **Reutilización**: Pueden ser llamadas desde múltiples lugares
3. **Testabilidad**: Fácil de testear de forma aislada
4. **Mantenibilidad**: Código organizado y fácil de mantener
5. **Transacciones**: Manejo seguro de operaciones complejas

---

## ⚡ Componentes Livewire

### WishlistButton

**Ubicación:** `app/Livewire/WishlistButton.php`

**Props:**
- `Product $product` - Producto a agregar/remover
- `?ProductVariant $variant` - Variante opcional

**Métodos:**
- `toggle()` - Agrega o remueve de wishlist según estado actual

**Vista:** `resources/views/livewire/wishlist-button.blade.php`

**Características:**
- Cambia de estado visual inmediatamente
- Muestra mensaje de feedback
- Dispara evento `wishlist-updated`
- Requiere autenticación

**Uso en Blade:**
```blade
<livewire:wishlist-button :product="$product" :variant="$selectedVariant" />
```

### WishlistPage

**Ubicación:** `app/Livewire/WishlistPage.php`

**Funcionalidades:**
- Lista todos los productos en wishlist del cliente
- Paginación automática
- Botón para remover de wishlist
- Enlaces a páginas de producto
- Muestra variantes si aplica

**Vista:** `resources/views/livewire/wishlist-page.blade.php`

**Características:**
- Grid responsive de productos
- Imágenes de productos
- Precios actualizados
- Estado vacío con mensaje

### AddressManager

**Ubicación:** `app/Livewire/AddressManager.php`

**Funcionalidades:**
- Lista todas las direcciones del cliente
- Formulario para agregar/editar direcciones
- Botón para establecer como predeterminada
- Eliminación con confirmación
- Validaciones en tiempo real

**Vista:** `resources/views/livewire/address-manager.blade.php`

**Características:**
- Formulario reactivo
- Badge para dirección predeterminada
- Botones de acción por dirección
- Feedback visual inmediato
- Validación de permisos

**Uso en Blade:**
```blade
<livewire:address-manager />
```

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### Catalog Domain

```
products
├── id
├── name
├── description
├── sku (unique)
├── price
├── stock
├── category_id (FK)
├── image_path
├── is_active
└── timestamps

product_variants
├── id
├── product_id (FK → products)
├── sku (unique)
├── price (nullable)
├── stock
├── image_path
├── is_active
└── timestamps

variant_attributes
├── id
├── variant_id (FK → product_variants)
├── attribute_id (FK → attributes)
├── value
└── timestamps
    └── UNIQUE(variant_id, attribute_id)
```

#### Sales Domain

```
orders
├── id
├── customer_id (FK → customers)
├── subtotal
├── tax
├── shipping_cost
├── total
├── status (enum: pending, paid, shipped, delivered, cancelled)
├── shipping_address
├── shipping_city
├── shipping_postal_code
└── timestamps

order_items
├── id
├── order_id (FK → orders)
├── product_id (FK → products)
├── quantity
├── price
├── subtotal
└── timestamps
```

#### Customers Domain

```
customers
├── id
├── name
├── email (unique)
├── password
├── phone
├── date_of_birth
├── email_verified_at
├── remember_token
└── timestamps

customer_addresses
├── id
├── customer_id (FK → customers)
├── street
├── city
├── state
├── postal_code
├── country
├── is_default
└── timestamps

wishlists
├── id
├── customer_id (FK → customers)
├── product_id (FK → products)
├── variant_id (FK → product_variants, nullable)
└── timestamps
    └── UNIQUE(customer_id, product_id, variant_id)
```

### Relaciones

```
products (1) ──< (N) product_variants
product_variants (N) >──< (N) attributes (variant_attributes)
products (1) ──< (N) wishlists
product_variants (1) ──< (N) wishlists
customers (1) ──< (N) wishlists
customers (1) ──< (N) customer_addresses
customers (1) ──< (N) orders
orders (1) ──< (N) order_items
order_items (N) ──> (1) products
```

---

## 💻 Ejemplos de Uso Completos

### 1. Crear Producto con Variantes

```php
use App\Domains\Catalog\Actions\CreateProductAction;
use App\Domains\Catalog\Actions\CreateProductVariantAction;

// 1. Crear producto base
$product = CreateProductAction::run([
    'name' => 'Camiseta Básica',
    'description' => 'Camiseta de algodón 100%',
    'sku' => 'CAM-001',
    'price' => 25.00,
    'stock' => 0, // Stock se maneja por variante
    'category_id' => 1,
    'is_active' => true,
]);

// 2. Crear variantes
$variant1 = CreateProductVariantAction::run(
    $product,
    ['stock' => 20],
    ['size' => 'S', 'color' => 'Rojo']
);

$variant2 = CreateProductVariantAction::run(
    $product,
    ['stock' => 15, 'price' => 27.00], // Precio diferente
    ['size' => 'M', 'color' => 'Azul']
);

$variant3 = CreateProductVariantAction::run(
    $product,
    ['stock' => 10],
    ['size' => 'L', 'color' => 'Verde', 'material' => 'Algodón Premium']
);
```

### 2. Flujo Completo de Orden con State Machine

```php
use App\Domains\Sales\Actions\CreateOrderAction;
use App\Domains\Sales\Actions\ChangeOrderStatusAction;
use App\Domains\Sales\States\OrderStatus;

// 1. Crear orden (estado inicial: pending)
$order = CreateOrderAction::run(
    $customer,
    [
        ['product_id' => 1, 'quantity' => 2, 'price' => 25.00],
        ['product_id' => 2, 'quantity' => 1, 'price' => 50.00],
    ],
    [
        'address' => 'Calle Principal 123',
        'city' => 'Ciudad de México',
        'postal_code' => '12345',
    ]
);

// 2. Cliente realiza pago → cambiar a 'paid'
$order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);

// 3. Orden es enviada → cambiar a 'shipped'
$order = ChangeOrderStatusAction::run($order, OrderStatus::SHIPPED);

// 4. Orden es entregada → cambiar a 'delivered'
$order = ChangeOrderStatusAction::run($order, OrderStatus::DELIVERED);

// Intentar cambiar de delivered a otro estado lanzará excepción
try {
    $order = ChangeOrderStatusAction::run($order, OrderStatus::SHIPPED);
} catch (InvalidArgumentException $e) {
    // "No se puede cambiar de 'delivered' a 'shipped'. Transiciones permitidas: "
}
```

### 3. Sistema de Wishlist Completo

```php
use App\Domains\Customers\Actions\AddToWishlistAction;
use App\Domains\Customers\Actions\RemoveFromWishlistAction;

// Agregar productos a wishlist
AddToWishlistAction::run($customer, $product1); // Sin variante
AddToWishlistAction::run($customer, $product2, $variant); // Con variante

// Verificar wishlist
$wishlistItems = $customer->wishlist()
    ->with(['product', 'variant'])
    ->get();

// Remover de wishlist
RemoveFromWishlistAction::run($customer, $product1);
RemoveFromWishlistAction::run($customer, $product2, $variant);
```

### 4. Gestión de Direcciones

```php
use App\Domains\Customers\Actions\SetDefaultAddressAction;

// Crear direcciones
$address1 = CustomerAddress::create([
    'customer_id' => $customer->id,
    'street' => 'Calle 1',
    'city' => 'Ciudad 1',
    'postal_code' => '12345',
    'country' => 'México',
]);

$address2 = CustomerAddress::create([
    'customer_id' => $customer->id,
    'street' => 'Calle 2',
    'city' => 'Ciudad 2',
    'postal_code' => '67890',
    'country' => 'México',
]);

// Establecer dirección predeterminada
SetDefaultAddressAction::run($customer, $address1);
// address1.is_default = true
// address2.is_default = false (automáticamente)

// Cambiar dirección predeterminada
SetDefaultAddressAction::run($customer, $address2);
// address1.is_default = false (automáticamente)
// address2.is_default = true

// Obtener dirección predeterminada
$defaultAddress = $customer->defaultAddress();
```

### 5. Uso en Controladores

```php
namespace App\Http\Controllers;

use App\Domains\Catalog\Actions\CreateProductVariantAction;
use App\Domains\Sales\Actions\ChangeOrderStatusAction;
use App\Domains\Sales\States\OrderStatus;
use App\Domains\Customers\Actions\AddToWishlistAction;

class ProductController extends Controller
{
    public function createVariant(Request $request, Product $product)
    {
        $variant = CreateProductVariantAction::run(
            $product,
            $request->only(['sku', 'price', 'stock']),
            $request->get('attributes', [])
        );

        return response()->json($variant);
    }
}

class OrderController extends Controller
{
    public function markAsPaid(Order $order)
    {
        $order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);
        
        // Disparar evento, enviar email, etc.
        
        return redirect()->back()->with('success', 'Orden marcada como pagada');
    }
}

class WishlistController extends Controller
{
    public function add(Product $product)
    {
        $customer = auth('customer')->user();
        
        AddToWishlistAction::run($customer, $product);
        
        return response()->json(['message' => 'Agregado a wishlist']);
    }
}
```

---

## 🔒 Validaciones y Seguridad

### State Machine

**Validaciones:**
- ✅ Solo permite transiciones válidas
- ✅ Previene cambios desde estados finales
- ✅ Lanza excepciones descriptivas
- ✅ Mensajes de error claros

**Ejemplo de Error:**
```php
// Orden en estado 'pending'
try {
    $order->transitionTo(OrderStatus::DELIVERED);
} catch (InvalidArgumentException $e) {
    // Mensaje: "No se puede cambiar de 'pending' a 'delivered'. 
    //           Transiciones permitidas: paid, cancelled"
}
```

### Wishlist

**Validaciones:**
- ✅ Un cliente no puede tener el mismo producto/variante dos veces
- ✅ Validación de permisos en componentes Livewire
- ✅ Verificación de propiedad antes de eliminar

**Constraint de Base de Datos:**
```php
$table->unique(['customer_id', 'product_id', 'variant_id']);
```

### Direcciones

**Validaciones:**
- ✅ Solo una dirección predeterminada por cliente
- ✅ Validación de propiedad antes de editar/eliminar
- ✅ Transacciones para garantizar consistencia
- ✅ Verificación de pertenencia al cliente

**Garantía de Consistencia:**
```php
// SetDefaultAddressAction usa transacción
DB::transaction(function () use ($customer, $address) {
    // Quitar flag de todas
    $customer->addresses()->update(['is_default' => false]);
    // Establecer nueva
    $address->update(['is_default' => true]);
});
```

---

## 📊 Diagramas de Flujo

### Flujo de Variantes de Producto

```
Producto Base
    │
    ├── Variante 1 (S, Rojo)
    │   ├── SKU: PROD-001-S-RED
    │   ├── Precio: $25.00
    │   └── Stock: 20
    │
    ├── Variante 2 (M, Azul)
    │   ├── SKU: PROD-001-M-BLU
    │   ├── Precio: $27.00 (específico)
    │   └── Stock: 15
    │
    └── Variante 3 (L, Verde, Algodón Premium)
        ├── SKU: PROD-001-L-GRE-ALG
        ├── Precio: $25.00 (del producto)
        └── Stock: 10
```

### Flujo de Estado de Orden

```
[Pendiente]
    │
    ├─ Pago recibido ──> [Pagado]
    │                        │
    │                        ├─ Enviado ──> [Enviado]
    │                        │                  │
    │                        │                  └─ Entregado ──> [Entregado] ⛔
    │                        │
    │                        └─ Cancelado ──> [Cancelado] ⛔
    │
    └─ Cancelado ──> [Cancelado] ⛔

⛔ = Estado Final (no puede cambiar)
```

### Flujo de Wishlist

```
Cliente
    │
    └── Wishlist
            │
            ├── Producto 1 (sin variante)
            │
            ├── Producto 2
            │   └── Variante: XL, Rojo
            │
            └── Producto 3
                └── Variante: M, Azul
```

---

## 🧪 Casos de Prueba Sugeridos

### Variantes de Producto

```php
// Test: Crear variante con SKU automático
$variant = CreateProductVariantAction::run($product, [], ['size' => 'XL']);
assert($variant->sku !== null);
assert($variant->sku !== $product->sku);

// Test: Precio efectivo usa variante o producto
$variantWithPrice = CreateProductVariantAction::run($product, ['price' => 30.00], []);
assert($variantWithPrice->effective_price === 30.00);

$variantWithoutPrice = CreateProductVariantAction::run($product, [], []);
assert($variantWithoutPrice->effective_price === $product->price);
```

### State Machine

```php
// Test: Transición válida
$order = Order::factory()->create(['status' => OrderStatus::PENDING]);
$order->transitionTo(OrderStatus::PAID);
assert($order->status === OrderStatus::PAID);

// Test: Transición inválida lanza excepción
$order = Order::factory()->create(['status' => OrderStatus::PENDING]);
expect(fn() => $order->transitionTo(OrderStatus::DELIVERED))
    ->toThrow(InvalidArgumentException::class);

// Test: Estado final no puede cambiar
$order = Order::factory()->create(['status' => OrderStatus::DELIVERED]);
assert($order->isFinal() === true);
expect(fn() => $order->transitionTo(OrderStatus::SHIPPED))
    ->toThrow(InvalidArgumentException::class);
```

### Wishlist

```php
// Test: No duplicados
AddToWishlistAction::run($customer, $product);
AddToWishlistAction::run($customer, $product); // No crea duplicado
assert($customer->wishlist()->count() === 1);

// Test: Variantes diferentes son items diferentes
AddToWishlistAction::run($customer, $product, $variant1);
AddToWishlistAction::run($customer, $product, $variant2);
assert($customer->wishlist()->count() === 2);
```

### Direcciones

```php
// Test: Solo una dirección predeterminada
$address1 = CustomerAddress::factory()->create(['customer_id' => $customer->id]);
$address2 = CustomerAddress::factory()->create(['customer_id' => $customer->id]);

SetDefaultAddressAction::run($customer, $address1);
SetDefaultAddressAction::run($customer, $address2);

assert($address1->fresh()->is_default === false);
assert($address2->fresh()->is_default === true);
```

---

## 📝 Notas de Implementación

### Consideraciones de Rendimiento

1. **Variantes:**
   - Considerar indexar `product_id` y `sku` en `product_variants`
   - Cachear variantes activas por producto

2. **Wishlist:**
   - Paginación implementada en `WishlistPage`
   - Eager loading de relaciones (`with(['product', 'variant'])`)

3. **State Machine:**
   - Validaciones rápidas (solo arrays)
   - No requiere consultas a BD para validar

### Mejoras Futuras

1. **Variantes:**
   - [ ] Sistema de precios por variante más complejo (descuentos, ofertas)
   - [ ] Alertas de stock bajo por variante
   - [ ] Historial de cambios de precio

2. **State Machine:**
   - [ ] Eventos para cada transición
   - [ ] Notificaciones automáticas
   - [ ] Historial de cambios de estado
   - [ ] Roles y permisos para transiciones

3. **Wishlist:**
   - [ ] Compartir wishlist
   - [ ] Múltiples listas por cliente
   - [ ] Notificaciones de precio reducido

4. **Direcciones:**
   - [ ] Validación de códigos postales
   - [ ] Autocompletado de direcciones
   - [ ] Integración con APIs de mapas

---

## 🚀 Próximos Pasos

1. **Ejecutar Migraciones:**
   ```bash
   php artisan migrate
   ```

2. **Configurar Rutas:**
   ```php
   // routes/web.php
   Route::get('/wishlist', \App\Livewire\WishlistPage::class)
       ->middleware('auth:customer')
       ->name('wishlist');
   ```

3. **Configurar Autenticación de Clientes:**
   ```php
   // config/auth.php
   'guards' => [
       'customer' => [
           'driver' => 'session',
           'provider' => 'customers',
       ],
   ],
   ```

4. **Probar Funcionalidades:**
   - Crear productos con variantes
   - Probar State Machine con órdenes
   - Usar wishlist y direcciones

---

## 📚 Referencias

- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [State Machine Pattern](https://refactoring.guru/design-patterns/state)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)

---

✅ **Implementación completa y lista para producción!**


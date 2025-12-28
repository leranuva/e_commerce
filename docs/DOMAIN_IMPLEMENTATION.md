# Implementación de los Dominios - Lógica de Negocio

## ✅ Implementación Completada

### 📦 Catalog Domain - Variantes de Producto

#### Migraciones Creadas
- ✅ `product_variants` - Tabla para variantes de productos
- ✅ `variant_attributes` - Tabla pivot para atributos de variantes

#### Modelos
- ✅ `ProductVariant` - Modelo con soporte para Media Library
- ✅ `Product` - Actualizado con relación a variantes

#### Action Classes
- ✅ `CreateProductVariantAction` - Crea variantes con atributos (talla, color, material)
  - Genera SKU único automáticamente
  - Asocia atributos a la variante
  - Maneja precios específicos por variante

#### Características
- **Sistema de Variantes Completo**: Permite productos con múltiples variantes (talla, color, material)
- **SKU Automático**: Genera SKUs únicos basados en el producto y atributos
- **Precios por Variante**: Cada variante puede tener su propio precio o usar el del producto
- **Stock por Variante**: Control de inventario independiente por variante
- **Imágenes por Variante**: Cada variante puede tener su propia imagen

### 💰 Sales Domain - State Machine para Órdenes

#### Implementación
- ✅ `OrderStatus` - Clase State Machine con transiciones válidas
- ✅ `Order` - Modelo actualizado con métodos de transición
- ✅ `ChangeOrderStatusAction` - Action para cambiar estados de forma segura

#### Estados y Transiciones

```
Pendiente → Pagado → Enviado → Entregado
    ↓          ↓
Cancelado  Cancelado
```

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

#### Uso del State Machine

```php
use App\Domains\Sales\Actions\ChangeOrderStatusAction;

// Cambiar estado de forma segura
$order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);

// O usar el método del modelo
$order->transitionTo(OrderStatus::SHIPPED);

// Verificar si puede cambiar
if ($order->canTransitionTo(OrderStatus::DELIVERED)) {
    // ...
}
```

### 👥 Customers Domain - Wishlist y Direcciones

#### Wishlist

**Migraciones:**
- ✅ `wishlists` - Tabla para lista de deseos

**Modelos:**
- ✅ `Wishlist` - Relación entre cliente, producto y variante

**Action Classes:**
- ✅ `AddToWishlistAction` - Agrega productos a la wishlist
- ✅ `RemoveFromWishlistAction` - Remueve productos de la wishlist

**Componentes Livewire:**
- ✅ `WishlistButton` - Botón reactivo para agregar/remover de wishlist
- ✅ `WishlistPage` - Página completa de wishlist con paginación

#### Direcciones Predeterminadas

**Action Classes:**
- ✅ `SetDefaultAddressAction` - Establece una dirección como predeterminada
  - Asegura que solo una dirección sea predeterminada por cliente
  - Usa transacciones para garantizar consistencia

**Componentes Livewire:**
- ✅ `AddressManager` - Gestor completo de direcciones
  - Agregar nuevas direcciones
  - Editar direcciones existentes
  - Establecer dirección predeterminada
  - Eliminar direcciones
  - Todo sin recargar la página

## 📁 Archivos Creados

### Migraciones
- `database/migrations/2025_12_27_210000_create_product_variants_table.php`
- `database/migrations/2025_12_27_210001_create_variant_attributes_table.php`
- `database/migrations/2025_12_27_210002_create_wishlists_table.php`

### Modelos
- `app/Domains/Catalog/Models/ProductVariant.php`
- `app/Domains/Customers/Models/Wishlist.php`

### States
- `app/Domains/Sales/States/OrderStatus.php`

### Action Classes
- `app/Domains/Catalog/Actions/CreateProductVariantAction.php`
- `app/Domains/Sales/Actions/ChangeOrderStatusAction.php`
- `app/Domains/Customers/Actions/AddToWishlistAction.php`
- `app/Domains/Customers/Actions/RemoveFromWishlistAction.php`
- `app/Domains/Customers/Actions/SetDefaultAddressAction.php`

### Componentes Livewire
- `app/Livewire/WishlistButton.php`
- `app/Livewire/WishlistPage.php`
- `app/Livewire/AddressManager.php`

### Vistas Livewire
- `resources/views/livewire/wishlist-button.blade.php`
- `resources/views/livewire/wishlist-page.blade.php`
- `resources/views/livewire/address-manager.blade.php`

## 🎯 Ejemplos de Uso

### Crear Variante de Producto

```php
use App\Domains\Catalog\Actions\CreateProductVariantAction;

$variant = CreateProductVariantAction::run(
    $product,
    [
        'sku' => 'PROD-001-XL-RED', // Opcional, se genera automáticamente
        'price' => 29.99, // Opcional, usa precio del producto si es null
        'stock' => 50,
    ],
    [
        'size' => 'XL',
        'color' => 'Rojo',
        'material' => 'Algodón',
    ]
);
```

### Cambiar Estado de Orden

```php
use App\Domains\Sales\Actions\ChangeOrderStatusAction;
use App\Domains\Sales\States\OrderStatus;

// Cambiar de pendiente a pagado
$order = ChangeOrderStatusAction::run($order, OrderStatus::PAID);

// Cambiar a enviado
$order = ChangeOrderStatusAction::run($order, OrderStatus::SHIPPED);

// Esto lanzará una excepción si la transición no es válida
// ChangeOrderStatusAction::run($order, OrderStatus::DELIVERED); // ❌ Error
```

### Agregar a Wishlist

```php
use App\Domains\Customers\Actions\AddToWishlistAction;

// Agregar producto sin variante
AddToWishlistAction::run($customer, $product);

// Agregar producto con variante específica
AddToWishlistAction::run($customer, $product, $variant);
```

### Establecer Dirección Predeterminada

```php
use App\Domains\Customers\Actions\SetDefaultAddressAction;

SetDefaultAddressAction::run($customer, $address);
// Automáticamente quita el flag de otras direcciones
```

## 🎨 Uso de Componentes Livewire

### WishlistButton en una Vista

```blade
{{-- En la página de producto --}}
<livewire:wishlist-button :product="$product" :variant="$selectedVariant" />
```

### AddressManager en Perfil de Cliente

```blade
{{-- En la página de perfil --}}
<livewire:address-manager />
```

### WishlistPage

```blade
{{-- Ruta: /wishlist --}}
<livewire:wishlist-page />
```

## 🔒 Validaciones y Seguridad

### State Machine
- ✅ Previene transiciones inválidas
- ✅ Lanza excepciones descriptivas
- ✅ Estados finales no pueden cambiar

### Wishlist
- ✅ Un cliente no puede tener el mismo producto/variante dos veces
- ✅ Validación de permisos en componentes Livewire

### Direcciones
- ✅ Solo una dirección predeterminada por cliente
- ✅ Validación de propiedad antes de editar/eliminar
- ✅ Transacciones para garantizar consistencia

## 📊 Estructura de Datos

### Product Variants
```
products (1) ──< (N) product_variants
product_variants (N) >──< (N) attributes (variant_attributes)
```

### Wishlist
```
customers (1) ──< (N) wishlists
products (1) ──< (N) wishlists
product_variants (1) ──< (N) wishlists (opcional)
```

### Order States
```
pending → paid → shipped → delivered
  ↓         ↓
cancelled cancelled
```

## 🚀 Próximos Pasos

1. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

2. **Crear rutas para componentes Livewire:**
   ```php
   Route::get('/wishlist', \App\Livewire\WishlistPage::class)->name('wishlist');
   ```

3. **Configurar autenticación de clientes:**
   - Crear guard 'customer' en `config/auth.php`
   - Crear middleware de autenticación

4. **Integrar componentes en vistas:**
   - Agregar `WishlistButton` en páginas de producto
   - Agregar `AddressManager` en perfil de cliente

---

✅ **Toda la lógica de negocio de los dominios está implementada y lista para usar!**


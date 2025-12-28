# Implementación del Frontend Reactivo con Livewire 3

## ✅ Implementación Completada

Se ha implementado un sistema completo de tienda (storefront) reactivo usando Livewire 3, con carrito de compras instantáneo y checkout integrado.

## 🏗️ Arquitectura

### Estructura de Componentes

```
app/
├── Services/
│   └── CartService.php          # Manejo del carrito (sesión/Redis)
├── Livewire/
│   ├── AddToCartButton.php      # Botón para agregar al carrito
│   ├── Cart.php                 # Vista del carrito
│   ├── CartCounter.php          # Contador en header
│   └── Checkout.php             # Proceso de checkout
└── Http/Controllers/
    ├── StorefrontController.php # Controlador del storefront
    └── CheckoutController.php   # Controlador de checkout
```

## 🛒 CartService

### Descripción

Service que maneja el carrito de compras usando la sesión de Laravel (compatible con Redis cuando esté configurado).

### Características

- ✅ Almacena items en sesión
- ✅ Compatible con Redis (automático cuando está configurado)
- ✅ Soporte para productos con variantes
- ✅ Validación de stock
- ✅ Cálculo automático de totales

### Métodos Principales

```php
// Agregar producto al carrito
$cartService->add($product, $quantity, $variant);

// Actualizar cantidad
$cartService->update($itemId, $quantity);

// Remover item
$cartService->remove($itemId);

// Limpiar carrito
$cartService->clear();

// Obtener items con productos cargados
$cartService->getItemsWithProducts();

// Calcular subtotal
$cartService->getSubtotal();
```

### Estructura de Items

```php
[
    'product_id' => 1,
    'variant_id' => null, // o ID de variante
    'quantity' => 2,
    'price' => 29.99,
    'name' => 'Producto - Variante',
    'image' => 'url_de_imagen'
]
```

## 🎨 Componentes Livewire

### AddToCartButton

**Ubicación:** `app/Livewire/AddToCartButton.php`

**Props:**
- `Product $product` - Producto a agregar
- `?ProductVariant $variant` - Variante opcional
- `int $quantity` - Cantidad inicial (default: 1)
- `bool $showQuantity` - Mostrar selector de cantidad

**Funcionalidades:**
- Agregar producto al carrito
- Validación de stock
- Incrementar/decrementar cantidad
- Feedback visual inmediato
- Dispara evento `cart-updated`

**Uso:**
```blade
<livewire:add-to-cart-button :product="$product" />
<livewire:add-to-cart-button :product="$product" :variant="$selectedVariant" />
```

### Cart

**Ubicación:** `app/Livewire/Cart.php`

**Funcionalidades:**
- Mostrar todos los items del carrito
- Actualizar cantidades
- Remover items
- Calcular totales (subtotal, IVA, total)
- Vaciar carrito
- Redirigir a checkout

**Características:**
- Actualización en tiempo real
- Validación de stock
- Cálculo automático de impuestos

### CartCounter

**Ubicación:** `app/Livewire/CartCounter.php`

**Funcionalidades:**
- Muestra total de items en el carrito
- Badge con número
- Se actualiza automáticamente cuando cambia el carrito
- Enlace a página del carrito

**Uso:**
```blade
<livewire:cart-counter />
```

### Checkout

**Ubicación:** `app/Livewire/Checkout.php`

**Funcionalidades:**
- Formulario de envío
- Carga dirección predeterminada si existe
- Resumen del pedido
- Cálculo de totales (subtotal, IVA, envío)
- Crear orden usando `CreateOrderAction`
- Limpiar carrito después de orden exitosa

**Integración:**
- Usa `CreateOrderAction` para crear la orden
- Valida autenticación de cliente
- Redirige a página de éxito

## 🛍️ Storefront

### Rutas

```php
// Página principal / Lista de productos
Route::get('/', [StorefrontController::class, 'index']);
Route::get('/products', [StorefrontController::class, 'index']);

// Detalle de producto
Route::get('/products/{product}', [StorefrontController::class, 'show']);

// Carrito
Route::get('/cart', \App\Livewire\Cart::class);

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index']);
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success']);
```

### Vistas

#### Layout Base (`layouts/app.blade.php`)

- Navegación con contador de carrito
- Footer
- Integración de Livewire y Tailwind

#### Lista de Productos (`storefront/products/index.blade.php`)

- Grid de productos
- Filtros por categoría
- Búsqueda
- Ordenamiento
- Paginación
- Integración de `AddToCartButton`

#### Detalle de Producto (`storefront/products/show.blade.php`)

- Imagen del producto
- Información completa
- Variantes disponibles
- Botones de acción (Agregar al carrito, Wishlist)
- Productos relacionados

#### Carrito (`livewire/cart.blade.php`)

- Lista de items
- Actualización de cantidades
- Cálculo de totales
- Botón para proceder al checkout

#### Checkout (`livewire/checkout.blade.php`)

- Formulario de envío
- Resumen del pedido
- Confirmación de orden

#### Éxito (`checkout/success.blade.php`)

- Confirmación de orden
- Detalles de la orden
- Enlace para continuar comprando

## 🔄 Flujo de Compra

```
1. Usuario navega productos
   ↓
2. Agrega productos al carrito (AddToCartButton)
   ↓
3. Carrito se actualiza instantáneamente (CartCounter)
   ↓
4. Usuario va al carrito (Cart component)
   ↓
5. Usuario procede al checkout (Checkout component)
   ↓
6. Usuario completa formulario de envío
   ↓
7. Se crea la orden (CreateOrderAction)
   ↓
8. Carrito se limpia
   ↓
9. Redirección a página de éxito
```

## 🎯 Características Destacadas

### Reactividad Instantánea

- ✅ Carrito se actualiza sin recargar página
- ✅ Contador en header se actualiza automáticamente
- ✅ Validaciones en tiempo real
- ✅ Feedback visual inmediato

### Integración con DDD

- ✅ Usa modelos de `App\Domains\Catalog\Models\Product`
- ✅ Usa `CreateOrderAction` del dominio Sales
- ✅ Respeta la arquitectura por dominios

### Experiencia de Usuario

- ✅ Interfaz moderna con Tailwind CSS
- ✅ Diseño responsive
- ✅ Mensajes de feedback claros
- ✅ Validaciones amigables

## 📝 Uso del CartService

### Inyección de Dependencias

El `CartService` se inyecta automáticamente en los componentes Livewire usando el método `boot()`:

```php
public function boot(CartService $cartService)
{
    $this->cartService = $cartService;
}
```

### Ejemplo de Uso

```php
// En un componente Livewire
public function addToCart()
{
    $this->cartService->add($this->product, $this->quantity, $this->variant);
    $this->dispatch('cart-updated');
}
```

## 🔧 Configuración

### Sesión

El carrito usa la sesión de Laravel. Para usar Redis:

1. Configurar Redis en `.env`
2. Cambiar `SESSION_DRIVER=redis`
3. El carrito funcionará automáticamente con Redis

### Autenticación

El checkout requiere autenticación de cliente. Asegúrate de tener:

- Guard `customer` configurado en `config/auth.php`
- Rutas de autenticación para clientes
- Middleware `auth:customer` en rutas protegidas

## 🚀 Próximos Pasos

1. **Implementar autenticación de clientes:**
   - Login/Register
   - Recuperación de contraseña
   - Perfil de cliente

2. **Mejorar checkout:**
   - Integración con pasarelas de pago
   - Múltiples métodos de envío
   - Cálculo dinámico de envío

3. **Optimizaciones:**
   - Cache de productos
   - Lazy loading de imágenes
   - Optimización de consultas

4. **Funcionalidades adicionales:**
   - Cupones de descuento
   - Comparación de productos
   - Historial de órdenes

---

✅ **Frontend reactivo completamente implementado y listo para usar!**


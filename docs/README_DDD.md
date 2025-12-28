# Arquitectura Domain-Driven Design (DDD)

Este proyecto utiliza una arquitectura DDD simplificada para mantener el código organizado y escalable.

## Estructura de Directorios

```
app/
├── Actions/                    # Clase base para todas las Actions
│   └── Action.php
│
└── Domains/                    # Dominios del negocio
    ├── Catalog/                # Dominio de Catálogo
    │   ├── Actions/            # Lógica de negocio
    │   │   └── CreateProductAction.php
    │   ├── Models/             # Modelos del dominio
    │   │   ├── Product.php
    │   │   ├── Category.php
    │   │   └── Attribute.php
    │   └── DataTransferObjects/ # DTOs para transferencia de datos
    │
    ├── Sales/                  # Dominio de Ventas
    │   ├── Actions/
    │   │   └── CreateOrderAction.php
    │   ├── Models/
    │   │   ├── Order.php
    │   │   └── OrderItem.php
    │   └── DataTransferObjects/
    │
    └── Customers/              # Dominio de Clientes
        ├── Actions/
        │   └── UpdateCustomerProfileAction.php
        ├── Models/
        │   ├── Customer.php
        │   └── CustomerAddress.php
        └── DataTransferObjects/
```

## Action Classes

Las **Action Classes** encapsulan la lógica de negocio específica, manteniendo los controladores delgados y el código organizado.

### Uso Básico

```php
// En lugar de poner toda la lógica en el controlador:
use App\Domains\Sales\Actions\CreateOrderAction;

// En el controlador:
public function store(Request $request)
{
    $order = CreateOrderAction::run(
        $customer,
        $cartItems,
        $shippingData
    );
    
    return response()->json($order);
}
```

### Ventajas

1. **Separación de responsabilidades**: La lógica de negocio está separada de los controladores
2. **Reutilización**: Las actions pueden ser llamadas desde múltiples lugares
3. **Testabilidad**: Fácil de testear de forma aislada
4. **Mantenibilidad**: Código más organizado y fácil de mantener

## Dominios

### Catalog (Catálogo)
Maneja productos, categorías y atributos.

### Sales (Ventas)
Maneja carrito, órdenes y pagos.

### Customers (Clientes)
Maneja perfiles, direcciones e historial de clientes.

## Próximos Pasos

1. Crear migraciones para los modelos
2. Crear Resource Classes para Filament
3. Implementar más Action Classes según sea necesario
4. Agregar validaciones y reglas de negocio


# Guía de Recursos de Filament con Estructura DDD

## 📋 Introducción

Esta guía explica cómo generar y configurar correctamente los recursos de Filament cuando se usa una estructura de carpetas personalizada por dominios (DDD).

## 🏗️ Estructura de Modelos por Dominio

Los modelos están organizados en dominios:

```
app/Domains/
├── Catalog/
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Attribute.php
│   │   └── ProductVariant.php
│   └── Actions/
├── Sales/
│   ├── Models/
│   │   ├── Order.php
│   │   └── OrderItem.php
│   └── Actions/
└── Customers/
    ├── Models/
    │   ├── Customer.php
    │   ├── CustomerAddress.php
    │   └── Wishlist.php
    └── Actions/
```

## ✅ Recursos Actuales y sus Modelos

### Catalog Domain

#### ProductResource
- **Modelo:** `App\Domains\Catalog\Models\Product`
- **Ubicación:** `app/Filament/Resources/ProductResource.php`
- **Grupo de Navegación:** "Catálogo"
- **Ícono:** `heroicon-o-shopping-bag`

#### CategoryResource
- **Modelo:** `App\Domains\Catalog\Models\Category`
- **Ubicación:** `app/Filament/Resources/CategoryResource.php`
- **Grupo de Navegación:** "Catálogo"
- **Ícono:** `heroicon-o-tag`

### Sales Domain

#### OrderResource
- **Modelo:** `App\Domains\Sales\Models\Order`
- **Ubicación:** `app/Filament/Resources/OrderResource.php`
- **Grupo de Navegación:** "Ventas"
- **Ícono:** `heroicon-o-shopping-cart`

### Customers Domain

#### CustomerResource
- **Modelo:** `App\Domains\Customers\Models\Customer`
- **Ubicación:** `app/Filament/Resources/CustomerResource.php`
- **Grupo de Navegación:** "Clientes"
- **Ícono:** `heroicon-o-users`

## 🚀 Generar Nuevos Recursos

### Comando Básico

```bash
php artisan make:filament-resource ModelName
```

### ⚠️ Importante: Especificar el Namespace Completo

Cuando generes un recurso para un modelo en un dominio, **debes especificar el namespace completo**:

```bash
# ❌ INCORRECTO - Buscará en app/Models/
php artisan make:filament-resource Product

# ✅ CORRECTO - Especifica el namespace completo
php artisan make:filament-resource "App\Domains\Catalog\Models\Product"
```

### Ejemplos por Dominio

#### Catalog Domain

```bash
# Product Variant
php artisan make:filament-resource "App\Domains\Catalog\Models\ProductVariant"

# Attribute
php artisan make:filament-resource "App\Domains\Catalog\Models\Attribute"
```

#### Sales Domain

```bash
# Order Item (si necesitas un recurso separado)
php artisan make:filament-resource "App\Domains\Sales\Models\OrderItem"
```

#### Customers Domain

```bash
# Customer Address
php artisan make:filament-resource "App\Domains\Customers\Models\CustomerAddress"

# Wishlist
php artisan make:filament-resource "App\Domains\Customers\Models\Wishlist"
```

## 🔧 Configuración Post-Generación

Después de generar un recurso, debes verificar y ajustar:

### 1. Verificar el Modelo

Asegúrate de que el recurso apunte al modelo correcto:

```php
<?php

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\Product; // ✅ Namespace correcto
use Filament\Resources\Resource;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class; // ✅ Modelo correcto
    
    // ...
}
```

### 2. Configurar Navegación

Agrega métodos de navegación según el dominio:

```php
public static function getNavigationIcon(): ?string
{
    return 'heroicon-o-shopping-bag';
}

public static function getNavigationGroup(): ?string
{
    return 'Catálogo'; // O 'Ventas', 'Clientes'
}

public static function getNavigationSort(): ?int
{
    return 1; // Orden en el menú
}
```

### 3. Configurar Relaciones

Si el modelo tiene relaciones con otros modelos del dominio, asegúrate de usar los namespaces correctos:

```php
// En el formulario
Forms\Components\Select::make('category_id')
    ->relationship('category', 'name') // ✅ Usa la relación del modelo
    ->searchable()
    ->preload(),

// En la tabla
Tables\Columns\TextColumn::make('category.name') // ✅ Acceso a relación
    ->searchable()
    ->sortable(),
```

### 4. Usar Enums y States Correctamente

Si el modelo usa enums o state machines, asegúrate de importarlos:

```php
use App\Domains\Sales\States\OrderStatus;

// En el formulario
Forms\Components\Select::make('status')
    ->options(OrderStatus::class) // ✅ Usa el enum
    ->required(),

// En la tabla
Tables\Columns\TextColumn::make('status')
    ->badge()
    ->color(fn (OrderStatus $state): string => $state->color()) // ✅ Métodos del enum
    ->formatStateUsing(fn (OrderStatus $state): string => $state->label()),
```

## 📝 Plantilla de Recurso Completo

```php
<?php

namespace App\Filament\Resources;

use App\Domains\{Domain}\Models\{Model};
use App\Filament\Resources\{Model}Resource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class {Model}Resource extends Resource
{
    protected static ?string $model = {Model}::class;
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-{icon}';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return '{Grupo}';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Formulario aquí
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columnas aquí
            ])
            ->filters([
                // Filtros aquí
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\List{Model}s::route('/'),
            'create' => Pages\Create{Model}::route('/create'),
            'edit' => Pages\Edit{Model}::route('/{record}/edit'),
        ];
    }
}
```

## 🔍 Verificación de Recursos

### Checklist Post-Generación

- [ ] El namespace del modelo es correcto (`App\Domains\{Domain}\Models\{Model}`)
- [ ] El `$model` apunta a la clase correcta
- [ ] Las relaciones usan los nombres correctos del modelo
- [ ] Los enums/states están importados correctamente
- [ ] El grupo de navegación corresponde al dominio
- [ ] Los filtros y acciones funcionan correctamente
- [ ] Las validaciones están configuradas

### Comandos de Verificación

```bash
# Verificar que no hay errores de sintaxis
php artisan about

# Limpiar caché de configuración
php artisan config:clear
php artisan cache:clear

# Verificar que Filament detecta los recursos
php artisan filament:list-resources
```

## 🐛 Problemas Comunes y Soluciones

### Error: "Class not found"

**Problema:** El recurso no encuentra el modelo.

**Solución:**
1. Verifica que el namespace del modelo sea correcto
2. Verifica que el modelo exista en la ubicación especificada
3. Ejecuta `composer dump-autoload`

### Error: "Relationship not found"

**Problema:** La relación no existe en el modelo.

**Solución:**
1. Verifica que la relación esté definida en el modelo
2. Verifica que el nombre de la relación sea correcto
3. Asegúrate de que el modelo relacionado exista

### Error: "Enum not found"

**Problema:** El enum no está importado o no existe.

**Solución:**
1. Verifica que el enum exista en el dominio correcto
2. Importa el enum al inicio del archivo del recurso
3. Usa el namespace completo: `App\Domains\{Domain}\States\{Enum}`

## 📚 Mejores Prácticas

### 1. Organización por Dominio

- Agrupa recursos relacionados en el mismo grupo de navegación
- Usa íconos consistentes dentro del mismo dominio
- Mantén el orden lógico en la navegación

### 2. Nombres Consistentes

- Usa nombres descriptivos para los recursos
- Mantén la convención: `{Model}Resource`
- Los grupos de navegación deben reflejar los dominios

### 3. Relaciones

- Siempre usa `->relationship()` en lugar de `->options()` cuando sea posible
- Preload relaciones comunes para mejor rendimiento
- Usa `->searchable()` en relaciones para mejor UX

### 4. Soft Deletes

Si el modelo usa SoftDeletes, agrega filtros y acciones:

```php
->filters([
    Tables\Filters\TrashedFilter::make(),
])
->actions([
    Tables\Actions\RestoreAction::make(),
    Tables\Actions\ForceDeleteAction::make(),
])
```

## 🎯 Ejemplo Completo: ProductVariantResource

```php
<?php

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Catalog\Models\Product;
use App\Filament\Resources\ProductVariantResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name') // ✅ Relación del modelo
                    ->searchable()
                    ->required()
                    ->preload(),
                
                Forms\Components\TextInput::make('sku')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01),
                
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->required(),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name') // ✅ Relación
                    ->searchable()
                    ->sortable()
                    ->label('Producto'),
                
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('MXN')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state < 10 ? 'danger' : 'success'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Activa'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
```

## 📖 Referencias

- [Documentación de Filament](https://filamentphp.com/docs)
- [Generación de Recursos](https://filamentphp.com/docs/resources/getting-started)
- [Relaciones en Filament](https://filamentphp.com/docs/forms/fields/select#relationships)

---

✅ **Todos los recursos actuales están correctamente configurados y apuntan a los modelos en la estructura DDD.**


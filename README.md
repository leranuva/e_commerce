# 🛒 E-Commerce Project

Proyecto de e-commerce moderno desarrollado con Laravel 12, Livewire 3, Filament 4 y arquitectura Domain-Driven Design (DDD).

## 📋 Tabla de Contenidos

- [Stack Tecnológico](#-stack-tecnológico)
- [Arquitectura](#-arquitectura)
- [Características](#-características)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Documentación](#-documentación)

## 🚀 Stack Tecnológico

### Backend
- **Laravel 12.11.0** (PHP 8.2+)
- **Livewire 3.7.3** - Componentes reactivos sin JavaScript
- **Filament 4.3.1** - Panel administrativo moderno y rápido

### Frontend
- **Tailwind CSS 4.1.18** - Framework de estilos utility-first
- **Vite 7.0.7** - Build tool moderno
- **Alpine.js** - Framework JavaScript ligero (incluido con Livewire)
- **Diseño inspirado en Cosmetics Store** - UI/UX moderna y elegante

### Base de Datos
- **MySQL 8.0+** - Base de datos relacional
- **Spatie Media Library 11.17.7** - Gestión profesional de archivos e imágenes

### Cache y Colas
- **Redis** (opcional) - Cache, sesiones y colas
- Configuración alternativa con `file` y `sync` para desarrollo

### Pagos
- **Stripe** - Gateway de pagos integrado
- **PaymentIntents** - Procesamiento seguro de pagos
- **Webhooks** - Confirmación automática de órdenes

## 🏗️ Arquitectura

### Domain-Driven Design (DDD)

El proyecto utiliza una arquitectura DDD simplificada para mantener el código organizado y escalable:

```
app/
├── Actions/                    # Clase base para Action Classes
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
    │   └── DataTransferObjects/
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

### Action Classes

Las **Action Classes** encapsulan la lógica de negocio, manteniendo los controladores delgados:

**Catalog Domain:**
- `CreateProductAction` - Crea productos con gestión de imágenes
- `CreateProductVariantAction` - Crea variantes de productos (talla, color, material)

**Sales Domain:**
- `CreateOrderAction` - Crea órdenes con cálculo automático de totales
- `ChangeOrderStatusAction` - Cambia estados de órdenes usando State Machine

**Customers Domain:**
- `UpdateCustomerProfileAction` - Actualiza perfiles de clientes
- `AddToWishlistAction` - Agrega productos a la wishlist
- `RemoveFromWishlistAction` - Remueve productos de la wishlist
- `SetDefaultAddressAction` - Establece dirección predeterminada

## ✨ Características

### Panel Administrativo (Filament)

✅ **CRUD Completo** para:
- Productos (con gestión de imágenes)
- Categorías (con soporte anidado)
- Órdenes (con estados y filtros)
- Clientes (con gestión de direcciones)

✅ **Dashboard en Tiempo Real**:
- Widget de ventas (día, mes, total)
- Alertas de stock bajo
- Estadísticas de órdenes

✅ **Gestión de Medios**:
- Spatie Media Library integrado
- Subida múltiple de imágenes
- Editor de imágenes integrado

### Modelos y Relaciones

- **Productos** ↔ Categorías (Many-to-One)
- **Productos** ↔ Variantes (One-to-Many)
- **Productos** ↔ Atributos (Many-to-Many)
- **Variantes** ↔ Atributos (Many-to-Many)
- **Órdenes** ↔ Clientes (Many-to-One)
- **Órdenes** ↔ Productos (Many-to-Many via OrderItems)
- **Clientes** ↔ Direcciones (One-to-Many)
- **Clientes** ↔ Wishlist (One-to-Many)

### Funcionalidades Implementadas

#### 📦 Catalog Domain - Variantes de Producto
- ✅ Sistema completo de variantes (talla, color, material)
- ✅ SKU automático por variante
- ✅ Precios y stock independientes por variante
- ✅ Imágenes específicas por variante

#### 💰 Sales Domain - State Machine
- ✅ Estado de máquina para órdenes
- ✅ Transiciones validadas: Pendiente → Pagado → Enviado → Entregado
- ✅ Previene cambios de estado inválidos
- ✅ Estados finales protegidos (Entregado, Cancelado)

#### 👥 Customers Domain - Wishlist y Direcciones
- ✅ Sistema de wishlist completo
- ✅ Soporte para productos con variantes
- ✅ Direcciones predeterminadas
- ✅ Componentes Livewire reactivos (sin recargar página)

## 📦 Instalación

### Requisitos Previos

- PHP 8.2 o superior
- Composer
- Node.js y npm
- MySQL 8.0+
- Redis (opcional, para producción)

### Pasos de Instalación

1. **Clonar el repositorio** (si aplica)

2. **Instalar dependencias de PHP:**
   ```bash
   composer install
   ```

3. **Instalar dependencias de Node:**
   ```bash
   npm install
   ```

4. **Configurar el entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos en `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=e_commerce_project
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Crear la base de datos:**
   ```sql
   CREATE DATABASE e_commerce_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

7. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

8. **Crear usuario administrador:**
   ```bash
   php artisan db:seed --class=AdminUserSeeder
   ```

9. **Compilar assets:**
   ```bash
   npm run dev
   # O para producción:
   npm run build
   ```

## ⚙️ Configuración

### Redis (Opcional)

Para desarrollo local, el proyecto funciona con drivers `file` y `sync`. Para usar Redis:

1. Instalar Redis (ver `docs/REDIS_INSTALLATION.md`)
2. Actualizar `.env`:
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

### Media Library

Las imágenes se almacenan en `storage/app/public`. Asegúrate de crear el enlace simbólico:

```bash
php artisan storage:link
```

## 🎯 Uso

### Iniciar el Servidor

```bash
php artisan serve
```

### Acceder al Panel Administrativo

- **URL:** http://localhost:8000/admin
- **Email:** admin@ecommerce.com
- **Password:** password

⚠️ **IMPORTANTE:** Cambia la contraseña después del primer inicio de sesión.

### Comandos Útiles

```bash
# Ver estado de migraciones
php artisan migrate:status

# Ejecutar seeders
php artisan db:seed

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Compilar assets en desarrollo
npm run dev

# Compilar assets para producción
npm run build
```

## 📁 Estructura del Proyecto

```
e_commerce_project/
├── app/
│   ├── Actions/                 # Clase base para Actions
│   ├── Domains/                 # Dominios DDD
│   │   ├── Catalog/             # Catálogo
│   │   ├── Sales/               # Ventas
│   │   └── Customers/           # Clientes
│   ├── Filament/                # Panel administrativo
│   │   ├── Resources/           # CRUD Resources
│   │   └── Widgets/             # Widgets del dashboard
│   └── Providers/
│       └── Filament/
│           └── AdminPanelProvider.php
│
├── database/
│   ├── migrations/              # Migraciones de BD
│   └── seeders/                 # Seeders
│
├── docs/                        # Documentación
│   ├── README.md                # Índice de documentación
│   ├── SETUP_COMPLETED.md       # Resumen de configuración
│   ├── README_DDD.md            # Arquitectura DDD
│   ├── REDIS_INSTALLATION.md    # Guía de Redis
│   ├── MIGRATIONS_READY.md      # Guía de migraciones
│   └── FILAMENT_IMPLEMENTATION.md # Panel administrativo
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│
└── public/                      # Archivos públicos
```

## 📚 Documentación

Toda la documentación detallada se encuentra en la carpeta `docs/`:

- **[README.md](docs/README.md)** - Índice de documentación
- **[SETUP_COMPLETED.md](docs/SETUP_COMPLETED.md)** - Resumen de configuración
- **[README_DDD.md](docs/README_DDD.md)** - Arquitectura Domain-Driven Design
- **[REDIS_INSTALLATION.md](docs/REDIS_INSTALLATION.md)** - Guía de instalación de Redis
- **[MIGRATIONS_READY.md](docs/MIGRATIONS_READY.md)** - Guía de migraciones
- **[FILAMENT_IMPLEMENTATION.md](docs/FILAMENT_IMPLEMENTATION.md)** - Panel administrativo
- **[DOMAIN_IMPLEMENTATION.md](docs/DOMAIN_IMPLEMENTATION.md)** - Implementación de lógica de negocio
- **[IMPLEMENTATION_SUMMARY.md](docs/IMPLEMENTATION_SUMMARY.md)** - Resumen completo de implementación

## 🎨 Recursos Adicionales

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Spatie Media Library](https://spatie.be/docs/laravel-medialibrary)

## 📝 Estado del Proyecto

### ✅ Completado

- [x] Instalación de Laravel 12
- [x] Configuración de Livewire 3
- [x] Instalación de Filament 4
- [x] Configuración de Tailwind CSS
- [x] Arquitectura DDD implementada
- [x] Action Classes creadas
- [x] Modelos y relaciones
- [x] Migraciones de base de datos
- [x] Resources de Filament (Product, Category, Order, Customer)
- [x] Widgets del dashboard (Ventas, Stock Bajo)
- [x] Spatie Media Library integrado
- [x] Usuario administrador configurado
- [x] Sistema de variantes de producto (Catalog Domain)
- [x] State Machine para órdenes (Sales Domain)
- [x] Sistema de wishlist (Customers Domain)
- [x] Direcciones predeterminadas (Customers Domain)
- [x] Componentes Livewire reactivos
- [x] Action Classes para toda la lógica de negocio
- [x] Frontend público con diseño moderno
- [x] Sistema de carrito de compras
- [x] Integración con Stripe
- [x] Autenticación de clientes
- [x] Webhooks de Stripe
- [x] Diseño responsive y animado

### ✅ Frontend Público

- [x] Diseño moderno inspirado en Cosmetics Store
- [x] Hero section con imagen de fondo y overlay
- [x] Cards de productos animadas con efectos hover
- [x] Sistema de navegación responsive
- [x] Sección de características (Season Sale, Free Shipping, Money Back)
- [x] Sección de testimonios
- [x] Newsletter subscription
- [x] Footer completo con información de contacto

### ✅ Sistema de Carrito y Checkout

- [x] Carrito de compras con Livewire 3
- [x] Gestión de sesión de carrito (Redis/File)
- [x] Validación de stock en tiempo real
- [x] Checkout con Stripe Integration
- [x] Webhooks de Stripe para confirmación de pagos
- [x] Guest checkout (carrito sin registro)

### ✅ Autenticación y Pagos

- [x] Sistema multi-auth (Admin y Customer)
- [x] Registro y login de clientes
- [x] Integración con Stripe (PaymentIntents)
- [x] Webhooks seguros para confirmación de órdenes
- [x] Protección CSRF en webhooks

### 🔄 En Progreso / Pendiente

- [ ] Tests automatizados
- [ ] Sistema de notificaciones por email
- [ ] Optimización de imágenes (lazy loading)
- [ ] SEO avanzado
- [ ] Sistema de cupones/descuentos

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores

- **Nombre** - *Ramiro Núñez Valverde*

---

<p align="center">Desarrollado usando Laravel, Livewire y Filament</p>

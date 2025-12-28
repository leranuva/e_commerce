# Documentación del Proyecto E-Commerce

Esta carpeta contiene toda la documentación del proyecto.

## Índice de Documentación

### 📋 [SETUP_COMPLETED.md](./SETUP_COMPLETED.md)
Resumen completo de la configuración inicial del proyecto, incluyendo:
- Stack tecnológico instalado
- Arquitectura DDD implementada
- Action Classes creadas
- Modelos organizados por dominio

### 🏗️ [README_DDD.md](./README_DDD.md)
Documentación completa de la arquitectura Domain-Driven Design:
- Estructura de directorios
- Uso de Action Classes
- Organización por dominios (Catalog, Sales, Customers)
- Ejemplos y mejores prácticas

### 🔴 [REDIS_INSTALLATION.md](./REDIS_INSTALLATION.md)
Guía completa para instalar y configurar Redis:
- Instalación del servidor Redis (Memurai, Docker, WSL2)
- Instalación de la extensión PHP Redis
- Configuración en Laravel
- Solución de problemas comunes

### 🗄️ [MIGRATIONS_READY.md](./MIGRATIONS_READY.md)
Documentación sobre las migraciones de base de datos:
- Lista de todas las migraciones creadas
- Estructura de tablas y relaciones
- Instrucciones para ejecutar migraciones
- Credenciales del usuario admin

### 🏛️ [DOMAIN_IMPLEMENTATION.md](./DOMAIN_IMPLEMENTATION.md)
Implementación completa de la lógica de negocio de los dominios:
- **Catalog Domain**: Sistema de variantes de producto (talla, color, material)
- **Sales Domain**: State Machine para órdenes (Pendiente → Pagado → Enviado → Entregado)
- **Customers Domain**: Wishlist y direcciones predeterminadas con Livewire
- Action Classes implementadas
- Componentes Livewire reactivos

### 📚 [FILAMENT_RESOURCES_GUIDE.md](./FILAMENT_RESOURCES_GUIDE.md)
Guía completa para generar y configurar recursos de Filament con estructura DDD:
- Cómo generar recursos apuntando a modelos en dominios
- Configuración correcta de namespaces
- Ejemplos de recursos por dominio
- Mejores prácticas y solución de problemas comunes

### 🎯 [ACTIONS_VS_SERVICES.md](./ACTIONS_VS_SERVICES.md)
Guía de arquitectura sobre cuándo usar Actions vs Services:
- Diferencias entre Actions y Services
- Casos de uso ideales para cada uno
- Decision tree para elegir el patrón correcto
- Mejores prácticas y ejemplos
- Patrón híbrido Action + Service

### 🔐 [AUTHENTICATION_AND_PAYMENTS.md](./AUTHENTICATION_AND_PAYMENTS.md)
Implementación de autenticación multi-auth y pagos:
- Configuración de guard 'customer'
- Guest Checkout (carrito sin login)
- Integración con Stripe
- PaymentService para manejo de pagos
- Optimizaciones de rendimiento (Lazy Loading, Entangled State)

### 🔒 [WEBHOOKS_SECURITY.md](./WEBHOOKS_SECURITY.md)
Sistema de seguridad para webhooks y prevención de órdenes sin pago:
- Validación de firmas de webhook de Stripe
- Órdenes pendientes que solo se confirman con webhook
- StripeWebhookController con medidas de seguridad
- Flujo seguro de checkout
- Prevención de manipulación de órdenes

## Estructura del Proyecto

```
app/
├── Actions/              # Clase base para Action Classes
├── Domains/              # Dominios del negocio
│   ├── Catalog/          # Productos, Categorías, Atributos
│   ├── Sales/            # Carrito, Órdenes, Pagos
│   └── Customers/        # Perfiles, Direcciones, Historial
└── ...
```

## Inicio Rápido

1. **Configurar base de datos:**
   ```bash
   # Crear base de datos en MySQL
   CREATE DATABASE e_commerce_project;
   ```

2. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

3. **Crear usuario admin:**
   ```bash
   php artisan db:seed --class=AdminUserSeeder
   ```

4. **Iniciar servidor:**
   ```bash
   php artisan serve
   ```

5. **Acceder al panel:**
   - URL: http://localhost:8000/admin
   - Email: admin@ecommerce.com
   - Password: password

## Recursos Adicionales

- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)


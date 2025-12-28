# Instalación de Redis para Windows

## ⚠️ Error Actual: "Class Redis not found"

Este error indica que **la extensión PHP Redis no está instalada**. Necesitas dos cosas:
1. **Servidor Redis** (Memurai, Docker, etc.)
2. **Extensión PHP Redis** (php_redis.dll)

## Solución Temporal (Ya Aplicada)

Se ha cambiado la configuración en `.env` para usar drivers que no requieren Redis:
- `CACHE_STORE=file`
- `SESSION_DRIVER=file`
- `QUEUE_CONNECTION=sync`

La aplicación ahora debería funcionar correctamente.

---

## Instalación Completa de Redis

Para usar Redis completamente, necesitas instalar tanto el servidor como la extensión PHP:

## Paso 1: Instalar Servidor Redis

### Opción 1: Memurai (Recomendado para Windows)

Memurai es una implementación nativa de Redis para Windows.

1. Descarga Memurai desde: https://www.memurai.com/get-memurai
2. Instala el ejecutable
3. El servicio se iniciará automáticamente
4. Redis estará disponible en `127.0.0.1:6379`

### Opción 2: Redis con WSL2 (Windows Subsystem for Linux)

Si tienes WSL2 instalado:

```bash
# En WSL2
sudo apt update
sudo apt install redis-server
sudo service redis-server start
```

### Opción 3: Docker (Recomendado si tienes Docker Desktop)

```bash
docker run -d -p 6379:6379 --name redis redis:latest
```

### Opción 4: Redis para Windows (No oficial)

1. Descarga desde: https://github.com/microsoftarchive/redis/releases
2. Extrae y ejecuta `redis-server.exe`
3. Mantén la ventana abierta o instálalo como servicio

## Paso 2: Instalar Extensión PHP Redis

### Para XAMPP con PHP 8.2:

1. **Descargar php_redis.dll:**
   - Visita: https://pecl.php.net/package/redis
   - O descarga desde: https://windows.php.net/downloads/pecl/releases/redis/
   - Busca la versión compatible con PHP 8.2 (Thread Safe)

2. **Instalar la extensión:**
   ```ini
   # Edita php.ini (ubicado en C:\xampp\php\php.ini)
   # Agrega esta línea en la sección de extensiones:
   extension=redis
   ```

3. **Copiar el archivo:**
   - Copia `php_redis.dll` a `C:\xampp\php\ext\`
   - Si falta `php_igbinary.dll`, también descárgalo y cópialo

4. **Reiniciar Apache:**
   - Desde el panel de control de XAMPP

5. **Verificar instalación:**
   ```bash
   php -m | findstr redis
   # Debería mostrar: redis
   ```

### Alternativa: Usar Predis (Cliente PHP puro, no requiere extensión)

Si prefieres no instalar la extensión, puedes usar Predis:

```bash
composer require predis/predis
```

Luego en `.env` cambia:
```env
REDIS_CLIENT=predis
```

## Verificar Instalación Completa

1. **Verificar servidor Redis:**
   ```bash
   redis-cli ping
   # Debería responder: PONG
   ```

2. **Verificar extensión PHP:**
   ```bash
   php -m | findstr redis
   # Debería mostrar: redis
   ```

3. **Probar desde PHP:**
   ```php
   <?php
   $redis = new Redis();
   $redis->connect('127.0.0.1', 6379);
   echo $redis->ping(); // Debería mostrar: +PONG
   ```

## Configuración en Laravel

Una vez instalado Redis, tu configuración en `.env` ya está lista:
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `REDIS_HOST=127.0.0.1`
- `REDIS_PORT=6379`

## Configuración Actual (Temporal)

Actualmente la aplicación está configurada para funcionar **sin Redis**:
- `CACHE_STORE=file` ✅
- `SESSION_DRIVER=file` ✅
- `QUEUE_CONNECTION=sync` ✅

Esto permite que la aplicación funcione correctamente, aunque con menor rendimiento que Redis.

## Cuando Tengas Redis Instalado

Una vez que tengas tanto el servidor Redis como la extensión PHP instalados, cambia en `.env`:
- `CACHE_STORE=redis`
- `SESSION_DRIVER=redis`
- `QUEUE_CONNECTION=redis`
- `REDIS_CLIENT=phpredis` (o `predis` si usas Predis)

## Recomendación

Para desarrollo local, **usar `file` y `sync` está bien**. Redis es más útil en producción o cuando necesitas:
- Cache compartido entre múltiples servidores
- Colas de trabajos asíncronas
- Sesiones compartidas en múltiples servidores

Para un proyecto pequeño o desarrollo local, los drivers `file` y `sync` son suficientes.


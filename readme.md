# Mi Proyecto - eCommerce

## Descripción
"Mi Proyecto" es una tienda en línea escalable desarrollada con **PHP**, **HTML**, **CSS**, y **JavaScript**. Utiliza **XAMPP** para la gestión de la base de datos y el servidor local.

## Estructura del Proyecto

```
mi_proyecto/
├── assets/
│   ├── css/
│   │   ├── styles.css
│   ├── images/
│   │   ├── img.jpg
│   ├── js/
│   │   ├── scripts.js
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── header.php
├── pages/
│   ├── carrito.php
│   ├── dashboard.php
│   ├── eliminar_carrito.php
│   ├── login.php
│   ├── register.php
│   ├── logout.php
├── .htaccess
├── .env
├── index.php
```

## Configuración de la Base de Datos

La base de datos **mi_proyecto** incluye las siguientes tablas:

```sql
CREATE DATABASE mi_proyecto;
USE mi_proyecto;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0
);

CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

## Configuración de `db.php`

El archivo `db.php` maneja la conexión segura a la base de datos:

```php
<?php
$dotenv = parse_ini_file(__DIR__ . '/../.env');

$host = $dotenv['DB_HOST'];
$db = $dotenv['DB_NAME'];
$user = $dotenv['DB_USER'];
$pass = $dotenv['DB_PASS'];
$charset = $dotenv['DB_CHARSET'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>
```

## Archivo `.env`

Contiene configuraciones sensibles para mejorar la seguridad:

```
DB_HOST=localhost
DB_NAME=mi_proyecto
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4
```

## Configuración del `index.php`

Archivo principal de la tienda, cargando el header y footer:

```php
<?php
include 'includes/header.php';
?>

<main>
    <h1>Bienvenido a Mi Proyecto</h1>
    <p>Explora nuestros productos.</p>
</main>

<?php
include 'includes/footer.php';
?>
```

## Estilos en `styles.css`

```css
body {
    background-color: #f4f4f4;
    font-family: Arial, sans-serif;
}

header {
    background: #007bff;
    color: white;
    padding: 15px;
    text-align: center;
}

footer {
    background: #333;
    color: white;
    text-align: center;
    padding: 15px;
}
```

---

Este documento se irá actualizando conforme avancemos en el desarrollo. 🚀


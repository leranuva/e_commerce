<?php
session_start();

// Determinar la ruta base dinámica
$base_url = (basename($_SERVER['PHP_SELF']) === 'index.php') ? '' : '../';
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Tienda Online</title>
  <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/styles.css">
</head>

<body>
  <header>
    <h1>🛍️ Mi Tienda Online</h1>
    <nav>
      <ul>
        <li><a href="<?php echo $base_url; ?>index.php">🏠 Inicio</a></li>
        <li><a href="<?php echo $base_url; ?>pages/carrito.php">🛒 Carrito</a></li>
        <li><a href="<?php echo $base_url; ?>pages/dashboard.php">📋 Dashboard</a></li>
        <?php if (isset($_SESSION['usuario_id'])): ?>
          <li><a href="<?php echo $base_url; ?>pages/logout.php">❌ Cerrar Sesión</a></li>
        <?php else: ?>
          <li><a href="<?php echo $base_url; ?>pages/login.php">🔑 Iniciar Sesión</a></li>
          <li><a href="<?php echo $base_url; ?>pages/register.php">📝 Registrarse</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </header>
<?php
session_start();

// Si el usuario no ha iniciado sesión, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
  header("Location: login.php");
  exit();
}

require '../includes/db.php'; // Conectar a la BD

// Obtener datos del usuario
try {
  $stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
  $stmt->execute([$_SESSION['usuario_id']]);
  $usuario = $stmt->fetch();
} catch (PDOException $e) {
  die("❌ Error al obtener datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
  <h1>Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?> 👋</h1>
  <p>Tu correo: <?php echo htmlspecialchars($usuario['email']); ?></p>

  <nav>
    <ul>
      <li><a href="carrito.php">🛒 Ver Carrito</a></li>
      <li><a href="logout.php">❌ Cerrar Sesión</a></li>
    </ul>
  </nav>
</body>

</html>
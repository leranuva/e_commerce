<?php
require '../includes/db.php'; // Conectar a la BD
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = $_POST['password'];

  try {
    $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password'])) {
      $_SESSION['usuario_id'] = $usuario['id'];
      $_SESSION['usuario_nombre'] = $usuario['nombre'];
      echo "✅ Inicio de sesión exitoso.";
    } else {
      echo "❌ Usuario o contraseña incorrectos.";
    }
  } catch (PDOException $e) {
    die("❌ Error en el login: " . $e->getMessage());
  }
}
?>

<form method="POST">
  <input type="email" name="email" placeholder="Correo electrónico" required>
  <input type="password" name="password" placeholder="Contraseña" required>
  <button type="submit">Iniciar sesión</button>
</form>
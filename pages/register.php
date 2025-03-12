<?php
require '../includes/db.php'; // Conectar a la BD
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
  $password = $_POST['password'];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Correo inválido.");
  }

  if (strlen($password) < 6) {
    die("❌ La contraseña debe tener al menos 6 caracteres.");
  }

  // Hash de la contraseña
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);

  try {
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $email, $passwordHash]);

    echo "✅ Usuario registrado correctamente.";
  } catch (PDOException $e) {
    die("❌ Error al registrar: " . $e->getMessage());
  }
}
?>

<form method="POST">
  <input type="text" name="nombre" placeholder="Nombre" required>
  <input type="email" name="email" placeholder="Correo electrónico" required>
  <input type="password" name="password" placeholder="Contraseña" required>
  <button type="submit">Registrarse</button>
</form>
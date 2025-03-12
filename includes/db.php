<?php
// Cargar variables de entorno desde .env
if (file_exists(__DIR__ . '/../.env')) {
  $env = parse_ini_file(__DIR__ . '/../.env');
} else {
  die("❌ Error: No se encontró el archivo .env");
}

// Configuración de conexión segura con PDO
try {
  $dsn = "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset={$env['DB_CHARSET']}";
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Modo de errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Modo de recuperación predeterminado
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Evita la emulación de consultas preparadas
  ];

  $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], $options);
  // echo "✅ Conexión exitosa a la base de datos"; // (Puedes descomentar para pruebas)
} catch (PDOException $e) {
  die("❌ Error en la conexión: " . $e->getMessage());
}

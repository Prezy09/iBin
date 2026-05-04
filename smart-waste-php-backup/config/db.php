<?php
// config/db.php - returns PDO using .env.php
function get_pdo(): PDO {
  $envPath = __DIR__ . '/../.env.php';
  if(!file_exists($envPath)){
    // fallback to .env.example.php if .env not created yet
    $envPath = __DIR__ . '/../.env.example.php';
  }
  $cfg = include $envPath;
  $host = $cfg['DB_HOST'] ?? 'localhost';
  $db   = $cfg['DB_NAME'] ?? 'smartwaste';
  $user = $cfg['DB_USER'] ?? 'root';
  $pass = $cfg['DB_PASS'] ?? '';
  $charset = $cfg['DB_CHARSET'] ?? 'utf8mb4';

  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];
  return new PDO($dsn, $user, $pass, $opt);
}

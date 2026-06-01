<?php
$configDir = __DIR__;

if (file_exists($configDir . '/db_config.php')) {
    require_once $configDir . '/db_config.php';
    return;
}

// Connessione Railway
$host     = 'mysql.railway.internal';
$dbname   = 'railway';
$user     = 'root';
$password = getenv('DB_PASSWORD') ?: '';
$port     = '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => "Errore di connessione: " . $e->getMessage()]));
}
<?php
session_start();

$baseDir = dirname(__DIR__);
if (file_exists($baseDir . '/config/db_config.php')) {
    require_once $baseDir . '/config/db_config.php';
} elseif (file_exists($baseDir . '/config/db_config_railway.php')) {
    require_once $baseDir . '/config/db_config_railway.php';
} else {
    $host     = getenv('DB_HOST')     ?: 'localhost';
    $dbname   = getenv('DB_NAME')     ?: 'task_manager_db';
    $user     = getenv('DB_USER')     ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $port     = getenv('DB_PORT')     ?: '3306';
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => "Errore di connessione: " . $e->getMessage()]));
    }
}

header('Content-Type: application/json');

// 1. Controllo sicurezza: l'utente è loggato?
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sessione non valida o scaduta"]);
    exit;
}

try {
    // 2. Query blindata: prendiamo SOLO i task dell'utente loggato
    $stmt = $pdo->prepare("SELECT id, titolo, testo, priorita, scadenza, lista_riferimento FROM tasks WHERE utente_id = ? ORDER BY id DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Risposta al frontend
    echo json_encode([
        "status" => "success",
        "data" => $tasks
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
}
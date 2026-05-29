<?php
session_start();

if (file_exists(__DIR__ . '/../config/db_config.php')) {
    require_once __DIR__ . '/../config/db_config.php';
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

$data = json_decode(file_get_contents("php://input"), true);

try {
    // Eliminiamo il task solo se l'ID coincide E l'utente_id è quello della sessione
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND utente_id = ?");
    $stmt->execute([$data['id'], $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Task eliminato"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Task non trovato o non autorizzato"]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
}

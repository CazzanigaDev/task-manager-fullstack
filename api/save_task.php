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

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION['user_id'];

try {
    if (isset($data['id']) && !empty($data['id'])) {
        // --- MODIFICA TASK ESISTENTE ---
        // Il WHERE controlla che l'ID del task appartenga all'utente in sessione
        $sql = "UPDATE tasks SET titolo = ?, testo = ?, priorita = ?, scadenza = ?, lista_riferimento = ? 
                WHERE id = ? AND utente_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['titolo'],
            $data['testo'],
            $data['priorita'],
            $data['scadenza'],
            $data['lista_riferimento'],
            $data['id'],
            $userId
        ]);
        echo json_encode([
            "status" => "success",
            "message" => "Task aggiornato",
            "id" => $data['id']
        ]);
    } else {
        // --- CREAZIONE NUOVO TASK ---
        $sql = "INSERT INTO tasks (utente_id, titolo, testo, priorita, scadenza, lista_riferimento) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $userId,
            $data['titolo'],
            $data['testo'],
            $data['priorita'],
            $data['scadenza'],
            $data['lista_riferimento']
        ]);
        echo json_encode([
            "status" => "success",
            "message" => "Task salvato",
            "id" => $pdo->lastInsertId()
        ]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
}
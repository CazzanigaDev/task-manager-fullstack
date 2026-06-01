<?php
session_start();

require_once __DIR__ . '/../config/db_bootstrap.php';

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

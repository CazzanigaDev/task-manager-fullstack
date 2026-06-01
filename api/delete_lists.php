<?php
session_start();

require_once __DIR__ . '/../config/db_bootstrap.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sessione non valida o scaduta"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION['user_id'];

if (!isset($data['titolo']) || empty(trim($data['titolo']))) {
    echo json_encode(["status" => "error", "message" => "Titolo della lista mancante"]);
    exit;
}

$titolo = trim($data['titolo']);

try {
    // Eliminiamo la lista basandoci sul titolo e sull'utente loggato
    $stmt = $pdo->prepare("DELETE FROM liste WHERE titolo = ? AND utente_id = ?");
    $stmt->execute([$titolo, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "success", "message" => "Lista eliminata correttamente"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lista non trovata o non autorizzata"]);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server durante l'eliminazione"]);
}
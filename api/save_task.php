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
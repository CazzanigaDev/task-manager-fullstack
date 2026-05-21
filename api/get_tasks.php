<?php
session_start();
require_once '../config/db_config.php';
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

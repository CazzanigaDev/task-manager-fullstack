<?php
session_start();

require_once __DIR__ . '/../config/db_bootstrap.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sessione non valida o scaduta"]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // 1. Controlliamo se l'utente ha almeno una lista
    $stmt = $pdo->prepare("SELECT id, titolo FROM liste WHERE utente_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    $liste = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Se non ha liste (nuovo utente), creiamo subito quella di default "Da smistare"
    if (empty($liste)) {
        $insert = $pdo->prepare("INSERT INTO liste (utente_id, titolo) VALUES (?, 'Da smistare')");
        $insert->execute([$userId]);
        
        // Recuperiamo nuovamente la lista appena creata per avere l'ID corretto
        $stmt->execute([$userId]);
        $liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "status" => "success",
        "data" => $liste
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
}
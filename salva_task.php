<?php
require_once 'config/db_config.php';

// Riceviamo i dati JSON dal JS
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    try {
        // Prepariamo la query (Sicurezza: Prepared Statements)
        $sql = "INSERT INTO tasks (titolo, testo, scadenza, priorita, lista_riferimento) 
                VALUES (:titolo, :testo, :scadenza, :priorita, :lista)";
        
        $stmt = $pdo->prepare($sql);
        
        // Eseguiamo passando i dati (Sanitizzazione automatica di PDO)
        $successo = $stmt->execute([
            ':titolo'   => htmlspecialchars(strip_tags($data['titolo'])),
            ':testo'    => htmlspecialchars(strip_tags($data['testo'])),
            ':scadenza' => $data['scadenza'],
            ':priorita' => $data['priorita'],
            ':lista'    => htmlspecialchars(strip_tags($data['listaRiferimento']))
        ]);

        if ($successo) {
            echo json_encode(['status' => 'success']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Dati non ricevuti']);
}
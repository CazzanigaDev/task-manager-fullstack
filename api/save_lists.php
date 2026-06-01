<?php
session_start();

$baseDir = dirname(__DIR__);

if (file_exists($baseDir . '/config/db_config.php')) {
    require_once $baseDir . '/config/db_config.php';
} elseif (file_exists($baseDir . '/config/db_config_railway.php')) {
    require_once $baseDir . '/config/db_config_railway.php';
} else {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Configurazione DB mancante']));
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Sessione non valida o scaduta"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $_SESSION['user_id'];

if (!isset($data['titolo']) || empty(trim($data['titolo']))) {
    echo json_encode(["status" => "error", "message" => "Il titolo della lista è obbligatorio"]);
    exit;
}

$titolo = trim($data['titolo']);

try {
    if (isset($data['id']) && !empty($data['id'])) {
        // --- MODIFICA / RINOMINA LISTA ---
        $listId = $data['id'];

        // 1. Recuperiamo il VECCHIO titolo della lista prima di cambiarlo (ci serve per aggiornare i task)
        $stmtOld = $pdo->prepare("SELECT titolo FROM liste WHERE id = ? AND utente_id = ?");
        $stmtOld->execute([$listId, $userId]);
        $vecchioTitolo = $stmtOld->fetchColumn();

        if (!$vecchioTitolo) {
            echo json_encode(["status" => "error", "message" => "Lista non trovata"]);
            exit;
        }

        // 2. Avviamo una transazione per fare entrambe le modifiche in sicurezza
        $pdo->beginTransaction();

        // A. Aggiorniamo il nome della lista nella tabella `liste`
        $stmtUpdateList = $pdo->prepare("UPDATE liste SET titolo = ? WHERE id = ? AND utente_id = ?");
        $stmtUpdateList->execute([$titolo, $listId, $userId]);

        // B. Aggiorniamo a cascata tutti i task legati al vecchio nome nella tabella `tasks`
        $stmtUpdateTasks = $pdo->prepare("UPDATE tasks SET lista_riferimento = ? WHERE lista_riferimento = ? AND utente_id = ?");
        $stmtUpdateTasks->execute([$titolo, $vecchioTitolo, $userId]);

        // Confermiamo le modifiche sul database
        $pdo->commit();

        echo json_encode([
            "status" => "success",
            "message" => "Lista e task associati aggiornati con successo",
            "id" => $listId
        ]);
    } else {
        // --- CREAZIONE NUOVA LISTA ---
        // Verifichiamo prima che non esista già una lista con lo stesso nome per questo utente
        $check = $pdo->prepare("SELECT id FROM liste WHERE utente_id = ? AND LOWER(titolo) = LOWER(?)");
        $check->execute([$userId, $titolo]);
        if ($check->fetch()) {
            echo json_encode(["status" => "error", "message" => "Esiste già una lista con questo nome"]);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO liste (utente_id, titolo) VALUES (?, ?)");
        $stmt->execute([$userId, $titolo]);

        echo json_encode([
            "status" => "success",
            "message" => "Lista creata",
            "id" => $pdo->lastInsertId()
        ]);
    }
    /**} catch (PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
} */
} catch (PDOException $e) {
    error_log($e->getMessage());
    // MODIFICA PER DEBUG: restituisce l'errore vero del database alla console
    echo json_encode(["status" => "error", "message" => "Errore DB: " . $e->getMessage()]);
}

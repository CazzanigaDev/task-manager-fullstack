<?php
session_start();
// Rimuove tutte le variabili di sessione
$_SESSION = array();

// Cancella il cookie della sessione se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distrugge definitivamente la sessione
session_destroy();

// Risposta per il frontend
header('Content-Type: application/json');
echo json_encode(["status" => "success", "message" => "Logout effettuato"]);
exit;
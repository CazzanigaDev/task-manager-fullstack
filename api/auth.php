<?php
session_start();

//codice per leggere il file env
$env = parse_ini_file(__DIR__ . '/../.env');
$captchaSecret = $env['RECAPTCHA_SECRET_KEY'] ?? '';

require_once '../config/db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Dati mancanti"]);
    exit;
}

$azione = $data['azione'];
$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$password = $data['password'];

// 1. [Controllo dati nuovo utente] Validazione Input
if (!$email) {
    echo json_encode(["status" => "error", "message" => "Formato email non valido"]);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(["status" => "error", "message" => "La password deve essere di almeno 8 caratteri"]);
    exit;
}

try {
    if ($azione === 'signup') {
        // ✅ STEP 1 — Controllo termini PRIMA di tutto
        if (!isset($data['termini']) || !$data['termini']) {
            echo json_encode(["status" => "error", "message" => "Devi accettare i termini."]);
            exit;
        }

        // ✅ STEP 2 — Verifica reCAPTCHA PRIMA di toccare il DB
        $captchaResponse = $data['captchaResponse'] ?? '';
        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify" .
                "?secret={$captchaSecret}&response={$captchaResponse}"
        );
        $responseData = json_decode($verify);

        if (!$responseData->success) {
            echo json_encode(["status" => "error", "message" => "Validazione Captcha fallita."]);
            exit;
        }

        // ✅ STEP 3 — Controllo email duplicata
        $check = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "Questa email è già registrata"]);
            exit;
        }

        // ✅ STEP 4 — Hash password e INSERT nel DB
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO utenti (email, password) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $passwordHash]);


        // ✅ STEP 5 — Sessione e risposta — UN SOLO echo
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_email'] = $email;

        echo json_encode([
            "status" => "success",
            "user" => $email,
            "message" => "Registrazione completata"
        ]);
        exit;
    } else if ($azione === 'login') {
        // 4. [Logica Login con password_verify]
        $stmt = $pdo->prepare("SELECT id, email, password FROM utenti WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verifichiamo se l'utente esiste e se la password hashata corrisponde
        if ($user && password_verify($password, $user['password'])) {
            // Rigeneriamo l'ID di sessione per sicurezza (evita session fixation)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            echo json_encode(["status" => "success", "user" => $user['email']]);
        } else {
            // Messaggio generico per non dare indizi ad eventuali hacker
            echo json_encode(["status" => "error", "message" => "Credenziali errate o inesistenti"]);
        }
    }
} catch (PDOException $e) {
    // Log dell'errore lato server
    error_log($e->getMessage());
    // Messaggio generico al frontend
    echo json_encode(["status" => "error", "message" => "Errore interno del server"]);
}

<?php
$railwayConfig = __DIR__ . '/config/db_config_railway.php';
echo "Cerco config in: " . $railwayConfig . "\n";
echo "Esiste: " . (file_exists($railwayConfig) ? 'SI' : 'NO') . "\n";

if (file_exists($railwayConfig)) {
    require_once $railwayConfig;
    echo "Config caricata!\n";
    try {
        $stmt = $pdo->query("SELECT 1");
        echo "Connessione DB: OK!\n";
    } catch (Exception $e) {
        echo "Errore DB: " . $e->getMessage() . "\n";
    }
}
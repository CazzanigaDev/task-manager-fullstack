<?php
var_dump($_ENV);
var_dump($_SERVER['DB_HOST'] ?? 'non trovato');
echo getenv('DB_HOST');
$all = getenv();
var_dump($all);
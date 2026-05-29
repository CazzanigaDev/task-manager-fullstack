<?php
echo phpversion() . "\n";
echo "PDO drivers: ";
print_r(PDO::getAvailableDrivers());
echo "\n";
echo "php.ini in uso: " . php_ini_loaded_file() . "\n";
echo "ini files extra: " . php_ini_scanned_files() . "\n";
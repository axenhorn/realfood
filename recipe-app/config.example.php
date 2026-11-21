<?php
// Kopiera till config.php UTANFÖR public/. Lägg aldrig lösenord i GitHub.
return [
 'dsn' => 'mysql:host=DIN_DB_HOST;dbname=DIN_DB;charset=utf8mb4',
 'user' => 'DIN_DB_USER',
 'password' => 'DITT_DB_PASSWORD',
 // Generera med: php -r 'echo password_hash(readline("Familjelösenord: "), PASSWORD_DEFAULT), PHP_EOL;'
 'family_password_hash' => '',
 'secure_cookies' => true,
];

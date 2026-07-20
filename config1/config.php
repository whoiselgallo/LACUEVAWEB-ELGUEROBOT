<?php
// Dify
define('DIFY_API_KEY', 'app-uAuHKtsI6l82PIqdF7e7yiVL');
define('DIFY_URL', 'https://api.dify.ai/v1/chat-messages');

// DB
define('DB_HOST', 'localhost');
define('DB_NAME', 'u115767692_el_guero_bot');
define('DB_USER', 'u115767692_lacueva');
define('DB_PASS', 'eldesmadredelGuero1');
define('DB_CHARSET', 'utf8mb4');

function db_connect() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}


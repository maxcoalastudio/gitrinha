<?php
// router.php - Versão simplificada
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// Se for API
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    include(__DIR__ . '/api/game_api.php');
    exit;
}

// Se for arquivo estático CSS
if (preg_match('/\.css$/', $requestUri)) {
    $file = __DIR__ . '/public' . $requestUri;
    if (file_exists($file)) {
        header('Content-Type: text/css');
        readfile($file);
        exit;
    }
}

// Se for PHJSP
if (strpos($requestUri, '.phjsp') !== false) {
    $file = __DIR__ . '/public/PHJSP/' . basename($requestUri);
    if (file_exists($file)) {
        include($file);
        exit;
    }
}

// Se for healthcheck
if ($requestUri === '/healthcheck.php') {
    echo "OK";
    exit;
}

// Qualquer outra coisa, mostra o index
include(__DIR__ . '/index.php');
?>

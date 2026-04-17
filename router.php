<?php
// router.php - Versão original que funcionava
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// API
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    include(__DIR__ . '/api/game_api.php');
    exit;
}

// Arquivos estáticos
$staticExtensions = ['css', 'js', 'jpg', 'png', 'gif', 'ico'];
$extension = pathinfo($requestUri, PATHINFO_EXTENSION);

if (in_array($extension, $staticExtensions)) {
    $filePath = __DIR__ . '/public' . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon'
        ];
        header('Content-Type: ' . ($mimeTypes[$extension] ?? 'text/plain'));
        readfile($filePath);
        exit;
    }
}

// PHJSP
if (strpos($requestUri, '.phjsp') !== false) {
    $filePath = __DIR__ . '/public' . $requestUri;
    if (file_exists($filePath)) {
        include($filePath);
        exit;
    }
}

// Rota principal
include(__DIR__ . '/index.php');
?>

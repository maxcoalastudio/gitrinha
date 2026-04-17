<?php
// router.php - Versão corrigida
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// API
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    include(__DIR__ . '/api/game_api.php');
    exit;
}

// Arquivos estáticos (CSS, JS, imagens)
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
$extension = pathinfo($requestUri, PATHINFO_EXTENSION);

if (in_array($extension, $staticExtensions)) {
    // Possíveis caminhos para o arquivo
    $possiblePaths = [
        __DIR__ . $requestUri,                           // /style.css
        __DIR__ . '/public' . $requestUri,               // /public/style.css
        __DIR__ . '/public/CSS' . $requestUri,           // /public/CSS/style.css
        __DIR__ . '/CSS' . $requestUri,                  // /CSS/style.css
        __DIR__ . '/public/css' . $requestUri,           // /public/css/style.css
    ];
    
    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml'
            ];
            header('Content-Type: ' . ($mimeTypes[$extension] ?? 'text/plain'));
            header('Cache-Control: public, max-age=3600');
            readfile($filePath);
            exit;
        }
    }
}

// PHJSP
if (strpos($requestUri, '.phjsp') !== false) {
    $possiblePaths = [
        __DIR__ . '/public/PHJSP/' . basename($requestUri),
        __DIR__ . '/PHJSP/' . basename($requestUri)
    ];
    foreach ($possiblePaths as $filePath) {
        if (file_exists($filePath)) {
            include($filePath);
            exit;
        }
    }
}

// Rota principal
include(__DIR__ . '/index.php');
?>

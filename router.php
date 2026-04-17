<?php
// router.php - Roteador principal
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// ============================================
// ARQUIVOS ESTÁTICOS (CSS, JS, imagens)
// ============================================
$staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
$extension = pathinfo($requestUri, PATHINFO_EXTENSION);

if (in_array($extension, $staticExtensions)) {
    // Procurar em várias possíveis localizações
    $paths = [
        __DIR__ . '/public' . $requestUri,
        __DIR__ . $requestUri,
        __DIR__ . '/public/CSS/style.css',
        __DIR__ . '/CSS/style.css'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path) && is_file($path)) {
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'png' => 'image/png'
            ];
            header('Content-Type: ' . ($mimeTypes[$extension] ?? 'text/plain'));
            header('Cache-Control: public, max-age=3600');
            readfile($path);
            exit;
        }
    }
}

// ============================================
// API
// ============================================
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    include(__DIR__ . '/api/game_api.php');
    exit;
}

// ============================================
// PHJSP
// ============================================
if (strpos($requestUri, '.phjsp') !== false) {
    $paths = [
        __DIR__ . '/public/PHJSP/' . basename($requestUri),
        __DIR__ . '/PHJSP/' . basename($requestUri)
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include($path);
            exit;
        }
    }
}

// ============================================
// ROTA PRINCIPAL
// ============================================
include(__DIR__ . '/index.php');
?>

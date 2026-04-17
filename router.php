<?php
// router.php - Roteador principal
$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// ============================================
// SERVIDOR DE ARQUIVOS ESTÁTICOS
// ============================================

// Mapeamento de extensões para MIME types
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

$extension = pathinfo($requestUri, PATHINFO_EXTENSION);

// Se for um arquivo estático
if (isset($mimeTypes[$extension])) {
    // Possíveis caminhos onde o arquivo pode estar
    $possiblePaths = [
        __DIR__ . $requestUri,                           // /style.css
        __DIR__ . '/public' . $requestUri,               // /public/style.css
        __DIR__ . '/public/CSS' . $requestUri,           // /public/CSS/style.css
        __DIR__ . '/CSS' . $requestUri,                  // /CSS/style.css
        __DIR__ . '/public/css' . $requestUri,           // /public/css/style.css (minúsculo)
        __DIR__ . '/css' . $requestUri                   // /css/style.css
    ];
    
    // Procurar o arquivo
    foreach ($possiblePaths as $path) {
        if (file_exists($path) && is_file($path)) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            header('Cache-Control: public, max-age=3600');
            readfile($path);
            exit;
        }
    }
    
    // Se não encontrou, log e continua
    error_log("Arquivo não encontrado: " . $requestUri);
}

// ============================================
// API
// ============================================
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    $apiFile = __DIR__ . '/api/game_api.php';
    if (file_exists($apiFile)) {
        include($apiFile);
    } else {
        echo json_encode(['success' => false, 'error' => 'API not found']);
    }
    exit;
}

// ============================================
// PHJSP
// ============================================
if (strpos($requestUri, '.phjsp') !== false) {
    $possiblePaths = [
        __DIR__ . '/public/PHJSP/' . basename($requestUri),
        __DIR__ . '/PHJSP/' . basename($requestUri)
    ];
    
    foreach ($possiblePaths as $path) {
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

<?php
// router.php - Roteador para Railway

// Habilitar log de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$requestUri = $_SERVER['REQUEST_URI'];
$requestUri = strtok($requestUri, '?');

// Log para debug
error_log("Router: " . $requestUri);

// ============================================
// API - Todas as requisições que começam com /api/
// ============================================
if (strpos($requestUri, '/api/') === 0) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    $apiFile = __DIR__ . '/api/game_api.php';
    
    if (file_exists($apiFile)) {
        $endpoint = substr($requestUri, 5);
        $endpoint = strtok($endpoint, '?');
        $_SERVER['API_ENDPOINT'] = $endpoint;
        include($apiFile);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'API file not found: ' . $apiFile]);
    }
    exit;
}

// ============================================
// Arquivos CSS
// ============================================
if (preg_match('/\.css$/', $requestUri)) {
    $paths = [
        __DIR__ . '/public' . $requestUri,
        __DIR__ . '/CSS' . $requestUri,
        __DIR__ . $requestUri
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            header('Content-Type: text/css');
            readfile($path);
            exit;
        }
    }
}

// ============================================
// Arquivos JavaScript
// ============================================
if (preg_match('/\.js$/', $requestUri)) {
    $paths = [
        __DIR__ . '/public' . $requestUri,
        __DIR__ . '/JS' . $requestUri,
        __DIR__ . $requestUri
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            header('Content-Type: application/javascript');
            readfile($path);
            exit;
        }
    }
}

// ============================================
// Arquivos PHJSP
// ============================================
if (strpos($requestUri, '.phjsp') !== false) {
    $paths = [
        __DIR__ . '/public/PHJSP/' . basename($requestUri),
        __DIR__ . '/PHJSP/' . basename($requestUri),
        __DIR__ . $requestUri
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            include($path);
            exit;
        }
    }
}

// ============================================
// Rota principal
// ============================================
if ($requestUri === '/' || $requestUri === '/index.php' || $requestUri === '/index') {
    include(__DIR__ . '/index.php');
    exit;
}

// ============================================
// Se não encontrou nada, mostrar index
// ============================================
include(__DIR__ . '/index.php');
?>

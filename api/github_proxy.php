<?php
// api/github_proxy.php - Proxy para API do GitHub

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$username = $_GET['username'] ?? '';
if (empty($username)) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

// Tentar buscar do GitHub
$url = "https://api.github.com/users/{$username}";
$options = [
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: GitRinha/1.0',
            'Accept: application/json'
        ],
        'timeout' => 15,
        'ignore_errors' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($url, false, $context);

if ($response !== false) {
    $data = json_decode($response, true);
    if (isset($data['login'])) {
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'User not found', 'message' => $data['message'] ?? '']);
    }
} else {
    echo json_encode(['error' => 'Failed to fetch user']);
}
?>

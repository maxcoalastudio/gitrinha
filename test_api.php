<?php
function fetchFromGitHub($username) {
    $url = "https://api.github.com/users/{$username}";
    
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: GitRinha/1.0',
                'Accept: application/json'
            ],
            'timeout' => 10
        ]
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'Falha na requisição'];
    }
    
    return json_decode($response, true);
}

// Testar
$user = 'maxcoalastudio';
$result = fetchFromGitHub($user);
echo "<pre>";
print_r($result);
echo "</pre>";
?>

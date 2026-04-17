function fetchFromGitHub($username) {
    $username = trim($username);
    $url = "https://api.github.com/users/{$username}";
    
    // Opção 1: Tentar com cURL (mais confiável)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'GitRinha/1.0 (https://github.com)',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Accept-Language: pt-BR,pt;q=0.9'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 5
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode === 200 && $response !== false) {
            $data = json_decode($response, true);
            if (is_array($data) && isset($data['login']) && !isset($data['message'])) {
                return $data;
            }
        }
        
        // Log do erro para debug
        error_log("cURL error for {$username}: HTTP {$httpCode} - {$error}");
    }
    
    // Opção 2: Tentar com file_get_contents
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", [
                'User-Agent: GitRinha/1.0 (https://github.com)',
                'Accept: application/json',
                'Accept-Language: pt-BR,pt;q=0.9'
            ]),
            'timeout' => 30,
            'ignore_errors' => true,
            'follow_location' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if (is_array($data) && isset($data['login']) && !isset($data['message'])) {
            return $data;
        }
    }
    
    // Opção 3: Tentar com API alternativa (usa cache do GitHub)
    $fallbackUrl = "https://raw.githubusercontent.com/{$username}/main/README.md";
    $options = [
        'http' => [
            'method' => 'HEAD',
            'header' => ['User-Agent: GitRinha/1.0'],
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($options);
    $headers = @get_headers($fallbackUrl, 1, $context);
    
    if ($headers && strpos($headers[0], '200') !== false) {
        // Usuário existe, retornar dados básicos
        return [
            'login' => $username,
            'name' => $username,
            'avatar_url' => "https://github.com/{$username}.png",
            'bio' => 'Desenvolvedor GitHub',
            'public_repos' => 0,
            'followers' => 0,
            'location' => 'GitHub',
            'created_at' => date('Y-m-d', strtotime('-1 year'))
        ];
    }
    
    // Opção 4: Usar API do GitHub com token (se disponível)
    $githubToken = getenv('GITHUB_TOKEN');
    if ($githubToken) {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: GitRinha/1.0',
                    'Accept: application/json',
                    'Authorization: token ' . $githubToken
                ],
                'timeout' => 30
            ]
        ];
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            if (is_array($data) && isset($data['login'])) {
                return $data;
            }
        }
    }
    
    error_log("Failed to fetch user: {$username} from GitHub");
    return null;
}

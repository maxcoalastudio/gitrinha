<?php
// api/game_api.php - API completa do jogo

$dataDir = __DIR__ . '/../data/';
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$usersFile = $dataDir . 'users.json';
$battlesFile = $dataDir . 'battles.json';
$scoresFile = $dataDir . 'scores.json';

// Inicializar arquivos
if (!file_exists($usersFile)) file_put_contents($usersFile, '[]');
if (!file_exists($battlesFile)) file_put_contents($battlesFile, '[]');
if (!file_exists($scoresFile)) file_put_contents($scoresFile, '{}');

$endpoint = $_SERVER['API_ENDPOINT'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'];

if (empty($endpoint)) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $endpoint = str_replace('/api/', '', $requestUri);
    $endpoint = strtok($endpoint, '?');
}

// Roteamento
if ($endpoint === 'ranking') {
    handleRanking($usersFile, $scoresFile);
} 
elseif ($endpoint === 'add_user' && $requestMethod === 'POST') {
    handleAddUser($usersFile, $scoresFile);
} 
elseif ($endpoint === 'battle' && $requestMethod === 'POST') {
    handleBattle($battlesFile, $scoresFile, $usersFile);
} 
elseif ($endpoint === 'init_warriors' && $requestMethod === 'POST') {
    handleInitWarriors($usersFile, $scoresFile);
} 
else {
    echo json_encode(['success' => false, 'error' => "Endpoint '{$endpoint}' não encontrado"]);
}

// ============================================
// FUNÇÕES
// ============================================

function handleRanking($usersFile, $scoresFile) {
    $users = json_decode(file_get_contents($usersFile), true);
    if (!is_array($users)) $users = [];
    
    $scores = json_decode(file_get_contents($scoresFile), true);
    if (!is_array($scores)) $scores = [];
    
    $ranking = [];
    foreach ($users as $user) {
        $login = $user['login'];
        $scoreData = isset($scores[$login]) && is_array($scores[$login]) ? $scores[$login] : ['score' => 1000, 'popularity' => 1.0, 'wins' => 0, 'losses' => 0];
        
        $totalBattles = $scoreData['wins'] + $scoreData['losses'];
        $winRate = $totalBattles > 0 ? round(($scoreData['wins'] / $totalBattles) * 100, 1) : 0;
        
        $ranking[] = [
            'login' => $login,
            'name' => $user['name'] ?? $login,
            'avatar_url' => "https://github.com/{$login}.png",
            'bio' => $user['bio'] ?? '',
            'public_repos' => $user['public_repos'] ?? 0,
            'followers' => $user['followers'] ?? 0,
            'location' => $user['location'] ?? '',
            'created_at' => $user['created_at'] ?? date('Y-m-d'),
            'score' => $scoreData['score'],
            'popularity' => $scoreData['popularity'],
            'wins' => $scoreData['wins'],
            'losses' => $scoreData['losses'],
            'win_rate' => $winRate
        ];
    }
    
    // Ordenar por score
    usort($ranking, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    echo json_encode([
        'success' => true,
        'ranking' => $ranking,
        'total' => count($ranking)
    ]);
}

function handleAddUser($usersFile, $scoresFile) {
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    
    if (empty($username)) {
        echo json_encode(['success' => false, 'error' => 'Digite um nome de usuário']);
        return;
    }
    
    // Carregar usuários existentes
    $users = json_decode(file_get_contents($usersFile), true);
    if (!is_array($users)) $users = [];
    
    // Verificar duplicado
    foreach ($users as $user) {
        if (strtolower($user['login']) === strtolower($username)) {
            echo json_encode(['success' => false, 'error' => "❌ Usuário @{$username} já está registrado!"]);
            return;
        }
    }
    
    // Buscar do GitHub
    $githubUser = fetchFromGitHub($username);
    
    // Se falhar, usar dados mock
    if (!$githubUser || !isset($githubUser['login'])) {
        $githubUser = [
            'login' => $username,
            'name' => $username,
            'avatar_url' => "https://github.com/{$username}.png",
            'bio' => 'Desenvolvedor GitHub',
            'public_repos' => 10,
            'followers' => 100,
            'location' => 'GitHub',
            'created_at' => date('Y-m-d', strtotime('-1 year'))
        ];
    }
    
    // Calcular pontuação inicial
    $accountAge = (time() - strtotime($githubUser['created_at'])) / (86400 * 30);
    $timeBonus = min($accountAge * 5, 100);
    $initialScore = ($githubUser['public_repos'] * 5) + ($githubUser['followers'] * 1) + $timeBonus + 500;
    
    $newUser = [
        'login' => $githubUser['login'],
        'name' => $githubUser['name'] ?? $githubUser['login'],
        'avatar_url' => "https://github.com/{$githubUser['login']}.png",
        'bio' => $githubUser['bio'] ?? 'Sem bio',
        'public_repos' => $githubUser['public_repos'],
        'followers' => $githubUser['followers'],
        'location' => $githubUser['location'] ?? 'Não informado',
        'created_at' => $githubUser['created_at'],
        'added_at' => date('Y-m-d H:i:s')
    ];
    
    $users[] = $newUser;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    
    // Salvar score
    $scores = json_decode(file_get_contents($scoresFile), true);
    if (!is_array($scores)) $scores = [];
    
    $scores[$githubUser['login']] = [
        'score' => $initialScore,
        'popularity' => 1.0,
        'wins' => 0,
        'losses' => 0
    ];
    file_put_contents($scoresFile, json_encode($scores, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'user' => $newUser,
        'initial_score' => $initialScore,
        'message' => "✅ @{$githubUser['login']} entrou na rinha!"
    ]);
}

function handleBattle($battlesFile, $scoresFile, $usersFile) {
    $input = json_decode(file_get_contents('php://input'), true);
    $winner = $input['winner'] ?? '';
    $loser = $input['loser'] ?? '';
    
    if (!$winner || !$loser) {
        echo json_encode(['success' => false, 'error' => 'Winner and loser required']);
        return;
    }
    
    if ($winner === $loser) {
        echo json_encode(['success' => false, 'error' => 'Não pode lutar contra si mesmo!']);
        return;
    }
    
    $scores = json_decode(file_get_contents($scoresFile), true);
    if (!is_array($scores)) $scores = [];
    
    // Inicializar scores se não existirem
    if (!isset($scores[$winner]) || !is_array($scores[$winner])) {
        $scores[$winner] = ['score' => 1000, 'popularity' => 1.0, 'wins' => 0, 'losses' => 0];
    }
    if (!isset($scores[$loser]) || !is_array($scores[$loser])) {
        $scores[$loser] = ['score' => 1000, 'popularity' => 1.0, 'wins' => 0, 'losses' => 0];
    }
    
    // Calcular novos valores
    $winnerPopularity = $scores[$winner]['popularity'] + 0.05;
    $loserPopularity = max(0.5, $scores[$loser]['popularity'] - 0.03);
    
    $pointsGained = round(10 * $winnerPopularity);
    $pointsLost = round(10 / $loserPopularity);
    
    $scores[$winner]['score'] += $pointsGained;
    $scores[$loser]['score'] = max(0, $scores[$loser]['score'] - $pointsLost);
    $scores[$winner]['popularity'] = round($winnerPopularity, 2);
    $scores[$loser]['popularity'] = round($loserPopularity, 2);
    $scores[$winner]['wins']++;
    $scores[$loser]['losses']++;
    
    file_put_contents($scoresFile, json_encode($scores, JSON_PRETTY_PRINT));
    
    // Buscar nomes dos usuários
    $users = json_decode(file_get_contents($usersFile), true);
    $winnerName = $winner;
    $loserName = $loser;
    foreach ($users as $user) {
        if ($user['login'] === $winner) $winnerName = $user['name'];
        if ($user['login'] === $loser) $loserName = $user['name'];
    }
    
    echo json_encode([
        'success' => true,
        'winner' => ['name' => $winnerName, 'new_score' => $scores[$winner]['score']],
        'loser' => ['name' => $loserName, 'new_score' => $scores[$loser]['score']],
        'battle' => ['points_gained' => $pointsGained, 'points_lost' => $pointsLost]
    ]);
}

function handleInitWarriors($usersFile, $scoresFile) {
    $input = json_decode(file_get_contents('php://input'), true);
    $usersList = $input['users'] ?? [];
    
    $users = json_decode(file_get_contents($usersFile), true);
    if (!is_array($users)) $users = [];
    
    $scores = json_decode(file_get_contents($scoresFile), true);
    if (!is_array($scores)) $scores = [];
    
    $added = [];
    foreach ($usersList as $username) {
        // Verificar se já existe
        $exists = false;
        foreach ($users as $user) {
            if (strtolower($user['login']) === strtolower($username)) {
                $exists = true;
                break;
            }
        }
        
        if ($exists) continue;
        
        $githubUser = fetchFromGitHub($username);
        
        // Se falhar, usar dados mock
        if (!$githubUser || !isset($githubUser['login'])) {
            $githubUser = [
                'login' => $username,
                'name' => $username,
                'avatar_url' => "https://github.com/{$username}.png",
                'bio' => 'Desenvolvedor',
                'public_repos' => 10,
                'followers' => 100,
                'location' => 'GitHub',
                'created_at' => date('Y-m-d', strtotime('-1 year'))
            ];
        }
        
        $accountAge = (time() - strtotime($githubUser['created_at'])) / (86400 * 30);
        $timeBonus = min($accountAge * 5, 100);
        $initialScore = ($githubUser['public_repos'] * 5) + ($githubUser['followers'] * 1) + $timeBonus + 500;
        
        $newUser = [
            'login' => $githubUser['login'],
            'name' => $githubUser['name'] ?? $githubUser['login'],
            'avatar_url' => "https://github.com/{$githubUser['login']}.png",
            'bio' => $githubUser['bio'] ?? 'Sem bio',
            'public_repos' => $githubUser['public_repos'],
            'followers' => $githubUser['followers'],
            'location' => $githubUser['location'] ?? 'Não informado',
            'created_at' => $githubUser['created_at'],
            'added_at' => date('Y-m-d H:i:s')
        ];
        
        $users[] = $newUser;
        $scores[$githubUser['login']] = [
            'score' => $initialScore,
            'popularity' => 1.0,
            'wins' => 0,
            'losses' => 0
        ];
        
        $added[] = $newUser['name'];
    }
    
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    file_put_contents($scoresFile, json_encode($scores, JSON_PRETTY_PRINT));
    
    echo json_encode([
        'success' => true,
        'added' => $added,
        'total' => count($added)
    ]);
}

function fetchFromGitHub($username) {
    $url = "https://api.github.com/users/" . urlencode($username);
    
    // Tentar com file_get_contents
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: GitRinha/1.0',
                'Accept: application/json'
            ],
            'timeout' => 10,
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
        if (is_array($data) && isset($data['login']) && !isset($data['message'])) {
            return $data;
        }
    }
    
    return null;
}
?>

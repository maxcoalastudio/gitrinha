<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Listar todos os guerreiros
        $warriors = json_decode(file_get_contents(WARRIORS_FILE), true);
        echo json_encode(['success' => true, 'warriors' => $warriors]);
        break;
        
    case 'POST':
        // Adicionar novo guerreiro
        $username = $input['username'] ?? '';
        if (!$username) {
            echo json_encode(['success' => false, 'error' => 'Username required']);
            exit;
        }
        
        // Buscar dados do GitHub
        $githubUser = fetchGitHubUser($username);
        if (!$githubUser) {
            echo json_encode(['success' => false, 'error' => 'User not found on GitHub']);
            exit;
        }
        
        $warriors = json_decode(file_get_contents(WARRIORS_FILE), true);
        
        // Verificar duplicado
        foreach ($warriors as $w) {
            if ($w['login'] === $githubUser['login']) {
                echo json_encode(['success' => false, 'error' => 'Warrior already exists']);
                exit;
            }
        }
        
        // Criar novo guerreiro
        $newWarrior = [
            'id' => uniqid(),
            'login' => $githubUser['login'],
            'name' => $githubUser['name'] ?? $githubUser['login'],
            'avatar_url' => $githubUser['avatar_url'],
            'bio' => $githubUser['bio'] ?? '',
            'public_repos' => $githubUser['public_repos'],
            'followers' => $githubUser['followers'],
            'created_at' => $githubUser['created_at'],
            'joined_at' => date('Y-m-d H:i:s'),
            'score' => calculateInitialScore($githubUser),
            'wins' => 0,
            'losses' => 0,
            'popularity_factor' => 1.0
        ];
        
        $warriors[] = $newWarrior;
        file_put_contents(WARRIORS_FILE, json_encode($warriors, JSON_PRETTY_PRINT));
        
        echo json_encode(['success' => true, 'warrior' => $newWarrior]);
        break;
        
    case 'PUT':
        // Atualizar guerreiro (após batalha)
        $warriorId = $input['id'] ?? '';
        $result = $input['result'] ?? ''; // 'win' or 'loss'
        
        $warriors = json_decode(file_get_contents(WARRIORS_FILE), true);
        $index = array_search($warriorId, array_column($warriors, 'id'));
        
        if ($index !== false) {
            if ($result === 'win') {
                $warriors[$index]['wins']++;
                $warriors[$index]['popularity_factor'] += 0.05;
            } else {
                $warriors[$index]['losses']++;
                $warriors[$index]['popularity_factor'] -= 0.03;
            }
            
            // Recalcular score
            $warriors[$index]['score'] = calculateDynamicScore(
                $warriorId,
                $warriors[$index]['score'],
                $warriors[$index]['wins'],
                $warriors[$index]['losses']
            );
            
            file_put_contents(WARRIORS_FILE, json_encode($warriors, JSON_PRETTY_PRINT));
            echo json_encode(['success' => true, 'warrior' => $warriors[$index]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Warrior not found']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

function fetchGitHubUser($username) {
    $url = "https://api.github.com/users/{$username}";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'GitRinha/1.0',
        CURLOPT_TIMEOUT => 10
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode === 200) ? json_decode($response, true) : null;
}
?>

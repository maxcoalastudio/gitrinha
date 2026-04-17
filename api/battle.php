<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$input = json_decode(file_get_contents('php://input'), true);
$winnerId = $input['winner_id'] ?? '';
$loserId = $input['loser_id'] ?? '';

if (!$winnerId || !$loserId) {
    echo json_encode(['success' => false, 'error' => 'Winner and loser required']);
    exit;
}

// Carregar dados
$warriors = json_decode(file_get_contents(WARRIORS_FILE), true);
$battles = json_decode(file_get_contents(BATTLES_FILE), true);

// Encontrar índices
$winnerIndex = array_search($winnerId, array_column($warriors, 'id'));
$loserIndex = array_search($loserId, array_column($warriors, 'id'));

if ($winnerIndex === false || $loserIndex === false) {
    echo json_encode(['success' => false, 'error' => 'Warriors not found']);
    exit;
}

// Atualizar estatísticas
$warriors[$winnerIndex]['wins']++;
$warriors[$winnerIndex]['popularity_factor'] += 0.05;
$warriors[$winnerIndex]['score'] = calculateDynamicScore(
    $winnerId,
    $warriors[$winnerIndex]['score'],
    $warriors[$winnerIndex]['wins'],
    $warriors[$winnerIndex]['losses']
);

$warriors[$loserIndex]['losses']++;
$warriors[$loserIndex]['popularity_factor'] -= 0.03;
$warriors[$loserIndex]['popularity_factor'] = max(0.5, $warriors[$loserIndex]['popularity_factor']);
$warriors[$loserIndex]['score'] = calculateDynamicScore(
    $loserId,
    $warriors[$loserIndex]['score'],
    $warriors[$loserIndex]['wins'],
    $warriors[$loserIndex]['losses']
);

// Registrar batalha
$battle = [
    'id' => uniqid(),
    'winner_id' => $winnerId,
    'winner_name' => $warriors[$winnerIndex]['name'],
    'loser_id' => $loserId,
    'loser_name' => $warriors[$loserIndex]['name'],
    'timestamp' => time(),
    'date' => date('Y-m-d H:i:s')
];
array_unshift($battles, $battle);
$battles = array_slice($battles, 0, 100);

// Salvar
file_put_contents(WARRIORS_FILE, json_encode($warriors, JSON_PRETTY_PRINT));
file_put_contents(BATTLES_FILE, json_encode($battles, JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'winner' => $warriors[$winnerIndex],
    'loser' => $warriors[$loserIndex],
    'score_change' => round($warriors[$winnerIndex]['score'] - $warriors[$loserIndex]['score'])
]);
?>

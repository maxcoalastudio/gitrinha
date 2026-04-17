<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$warriors = json_decode(file_get_contents(WARRIORS_FILE), true);

// Ordenar por score (decrescente)
usort($warriors, function($a, $b) {
    return $b['score'] - $a['score'];
});

// Adicionar informações extras
foreach ($warriors as &$warrior) {
    $totalBattles = $warrior['wins'] + $warrior['losses'];
    $warrior['win_rate'] = $totalBattles > 0 ? round(($warrior['wins'] / $totalBattles) * 100, 1) : 0;
    $warrior['total_battles'] = $totalBattles;
}

echo json_encode([
    'success' => true,
    'ranking' => $warriors,
    'total_warriors' => count($warriors),
    'updated_at' => date('Y-m-d H:i:s')
]);
?>

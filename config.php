<?php
// config.php - Configurações do sistema

define('DATA_PATH', __DIR__ . '/data/');
define('WARRIORS_FILE', DATA_PATH . 'warriors.json');
define('BATTLES_FILE', DATA_PATH . 'battles.json');
define('SCORE_FILE', DATA_PATH . 'score_history.json');

// Fórmula de pontuação inicial
function calculateInitialScore($user) {
    // Fator base: (repos * 10) + (followers * 1)
    $baseScore = ($user['public_repos'] * 10) + ($user['followers'] * 1);
    
    // Fator tempo: quanto mais antigo, mais bônus (máx 100 pontos)
    $accountAge = (time() - strtotime($user['created_at'])) / (86400 * 365);
    $timeBonus = min($accountAge * 10, 100);
    
    // Fator bio: se tiver bio completa, bônus de 20 pontos
    $bioBonus = (strlen($user['bio'] ?? '') > 50) ? 20 : 0;
    
    return round($baseScore + $timeBonus + $bioBonus);
}

// Função para recalcular pontuação com base nas batalhas
function calculateDynamicScore($userId, $currentScore, $wins, $losses) {
    // Fator de popularidade: cada vitória aumenta 5%, cada derrota diminui 3%
    $popularityFactor = 1 + (($wins * 0.05) - ($losses * 0.03));
    
    // Limitar fator entre 0.5 e 2.0
    $popularityFactor = max(0.5, min(2.0, $popularityFactor));
    
    return round($currentScore * $popularityFactor);
}

// Inicializar arquivos de dados
function initDataFiles() {
    if (!file_exists(DATA_PATH)) {
        mkdir(DATA_PATH, 0777, true);
    }
    
    if (!file_exists(WARRIORS_FILE)) {
        file_put_contents(WARRIORS_FILE, json_encode([]));
    }
    
    if (!file_exists(BATTLES_FILE)) {
        file_put_contents(BATTLES_FILE, json_encode([]));
    }
    
    if (!file_exists(SCORE_FILE)) {
        file_put_contents(SCORE_FILE, json_encode([]));
    }
}

initDataFiles();
?>

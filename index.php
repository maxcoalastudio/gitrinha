<?php
require_once('public/PHJSP/PARSE.phjsp');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitRinha - Batalha de Devs</title>
    <link href="/CSS/style.css" rel="stylesheet" type="text/css">
</head>
<body id="body">
    <h1 id="titulo">⚔️ GitRinha ⚔️</h1>
    
    <div class="main-container">
    <!-- LADO ESQUERDO - RANKING E CONCORRENTES -->
    <div class="left-panel">
        <!-- Ranking TOP 10 -->
        <div class="ranking-panel">
            <h3>🏆 TOP 10 Ranking 🏆</h3>
            <div id="ranking-list" class="ranking-list">
                <p>Carregando ranking...</p>
            </div>
        </div>
        
        <!-- Concorrentes -->
        <div class="concorrentes-panel">
            <h3>📋 Concorrentes</h3>
            <div id="concorrentes-list" class="concorrentes-list">
                <p>Carregando...</p>
            </div>
        </div>
    </div>
    
    <!-- CENTRO - CARDS COM VS -->
    <div class="battle-area">
        <h2>🔥 Escolha seu guerreiro! 🔥</h2>
        <div class="battle-container">
            <div id="card1" class="battle-card-container"></div>
            <div class="vs-divider">VS</div>
            <div id="card2" class="battle-card-container"></div>
        </div>
        <div id="battle-result"></div>
        <div id="game-messages"></div>
    </div>
</div>
    
    <!-- Waiting Message -->
    <div id="waiting-message" class="waiting-container" style="display: flex;">
        <div class="waiting-card">
            <h2>⚔️ Aguardando Guerreiros ⚔️</h2>
            <p>Carregando dados do servidor...</p>
            <div class="waiting-progress">
                <div class="progress-bar" style="width: 50%"></div>
            </div>
        </div>
    </div>
    
    <!-- Add User Section -->
    <div class="add-user-section">
        <h3>➕ Adicionar participante</h3>
        <input type="text" id="github-username" placeholder="Username do GitHub">
        <button onclick="addnewuser(document.getElementById('github-username').value)">Adicionar</button>
    </div>
    
    <!-- FOOTER - INFORMAÇÕES DO CRIADOR E APOIO -->
    <footer class="footer">
        <div class="footer-content">
            <!-- Criador -->
            <div class="footer-creator">
                <span class="creator-name">Maxwell Araujo Santos</span>
                <a href="mailto:maxwell.modelador@gmail.com" class="creator-email">maxwell.modelador@gmail.com</a>
            </div>
            
            <!-- PIX para apoio -->
            <div class="footer-pix" onclick="openPixModal()">
                <div class="pix-label">
                    <span>Seja um Apoiador</span>
                    <button class="support-btn">❤️ Apoiar com PIX</button>
                </div>
                <div class="pix-key">
                    Chave PIX: 891e8e49-c1aa-4e66-ba53-5d4106b8a73a
                </div>
            </div>
            
            <!-- Redes Sociais -->
            <div class="footer-social">
                <a href="https://orby-l9d1.onrender.com/" target="_blank" class="social-link orby">
                    🌐 Orby (em desenvolvimento)
                </a>
                <a href="https://github.com/maxcoalastudio" target="_blank" class="social-link github">
                    🐙 GitHub
                </a>
            </div>
        </div>
    </footer>
    
    <!-- Modal PIX -->
    <div id="pixModal" class="pix-modal">
        <div class="pix-modal-content">
            <span class="pix-modal-close" onclick="closePixModal()">&times;</span>
            <h2>💎 Apoie o Projeto 💎</h2>
            <p>Sua contribuição ajuda a manter o GitRinha vivo e em desenvolvimento!</p>
            
            <div class="pix-qr">
                <div class="pix-qr-placeholder">
                    💰 PIX 💰
                </div>
            </div>
            
            <div class="pix-code" id="pixCode" onclick="copyPixKey()">
                891e8e49-c1aa-4e66-ba53-5d4106b8a73a
            </div>
            
            <button class="copy-btn" onclick="copyPixKey()">📋 Copiar Chave PIX</button>
            
            <p style="font-size: 12px; margin-top: 20px;">
                ✅ Sem taxas | ✅ Doação voluntária | ✅ Qualquer valor
            </p>
        </div>
    </div>
    
    <div id="app" style="display: none;"></div>
    
    <?php
    require_once('public/PHJSP/scripts.php');
    require_once('public/PHJSP/game.phjsp');
    ?>

    <div class="music-player">
    <audio id="bgm" loop autoplay>
        <source src="public/music/battle-theme.wav" type="audio/mpeg">
        Seu navegador não suporta áudio.
    </audio>
    <button id="musicToggle" class="music-toggle">🔊</button>
    </div>
    
    <script>
    // Funções do Modal PIX
    function openPixModal() {
        document.getElementById('pixModal').style.display = 'flex';
    }
    
    function closePixModal() {
        document.getElementById('pixModal').style.display = 'none';
    }
    
    function copyPixKey() {
        const pixKey = '891e8e49-c1aa-4e66-ba53-5d4106b8a73a';
        navigator.clipboard.writeText(pixKey).then(function() {
            showmessage('✅ Chave PIX copiada! Obrigado por apoiar!', 'success');
        }, function() {
            showmessage('❌ Erro ao copiar. Copie manualmente.', 'error');
        });
    }
    
    // Fechar modal ao clicar fora
    window.onclick = function(event) {
        const modal = document.getElementById('pixModal');
        if (event.target === modal) {
            closePixModal();
        }
    }
    // Controle de música
const bgm = document.getElementById('bgm');
const toggleBtn = document.getElementById('musicToggle');

// Tentar tocar automaticamente (navegadores podem bloquear)
document.addEventListener('click', function initAudio() {
    bgm.play().catch(e => console.log('Autoplay bloqueado'));
    document.removeEventListener('click', initAudio);
}, { once: true });

// Toggle play/pause
toggleBtn.addEventListener('click', function() {
    if (bgm.paused) {
        bgm.play();
        toggleBtn.textContent = '🔊';
    } else {
        bgm.pause();
        toggleBtn.textContent = '🔇';
    }
});

// Salvar preferência no localStorage
bgm.addEventListener('play', () => localStorage.setItem('bgmPlaying', 'true'));
bgm.addEventListener('pause', () => localStorage.setItem('bgmPlaying', 'false'));

// Restaurar estado
if (localStorage.getItem('bgmPlaying') === 'false') {
    bgm.pause();
    toggleBtn.textContent = '🔇';
}
    </script>
</body>
</html>

// game.js - Sistema completo do GitRinha

let warriors = [];
let currentBattle = null;

// Carregar ranking
async function loadRanking() {
    try {
        const response = await fetch('/api/ranking.php');
        const data = await response.json();
        
        if (data.success) {
            warriors = data.ranking;
            renderRanking(warriors);
            renderConcorrentes(warriors.slice(10));
            
            if (warriors.length >= 2 && !currentBattle) {
                startNewBattle();
            }
        }
    } catch (error) {
        console.error('Erro ao carregar ranking:', error);
    }
}

// Renderizar TOP 10
function renderRanking(warriors) {
    const top10 = warriors.slice(0, 10);
    const rankingDiv = document.getElementById('ranking-list');
    
    if (!rankingDiv) return;
    
    rankingDiv.innerHTML = top10.map((warrior, index) => {
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}`;
        const winRate = warrior.win_rate || 0;
        
        return `
            <div class="ranking-item ${index === 0 ? 'first-place' : ''}">
                <span class="rank-medal">${medal}</span>
                <img src="${warrior.avatar_url}" class="rank-avatar" alt="${warrior.name}">
                <div class="rank-info">
                    <div class="rank-name">${escapeHtml(warrior.name)}</div>
                    <div class="rank-stats">
                        <span>⭐ ${warrior.score} pts</span>
                        <span>🏆 ${warrior.wins}W</span>
                        <span>📊 ${winRate}%</span>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// Renderizar lista completa de concorrentes (com scroll)
function renderConcorrentes(warriors) {
    const concorrentesDiv = document.getElementById('concorrentes-list');
    
    if (!concorrentesDiv) return;
    
    if (warriors.length === 0) {
        concorrentesDiv.innerHTML = '<div class="empty-message">Nenhum concorrente ainda</div>';
        return;
    }
    
    concorrentesDiv.innerHTML = warriors.map((warrior, index) => {
        const position = index + 11;
        const winRate = warrior.win_rate || 0;
        
        return `
            <div class="concorrente-item">
                <span class="concorrente-position">#${position}</span>
                <img src="${warrior.avatar_url}" class="concorrente-avatar" alt="${warrior.name}">
                <div class="concorrente-info">
                    <div class="concorrente-name">${escapeHtml(warrior.name)}</div>
                    <div class="concorrente-stats">
                        <span>⭐ ${warrior.score} pts</span>
                        <span>🏆 ${warrior.wins}W</span>
                        <span>📉 ${warrior.losses}L</span>
                    </div>
                </div>
                <div class="concorrente-winrate">
                    <div class="winrate-bar" style="width: ${winRate}%"></div>
                    <span>${winRate}%</span>
                </div>
            </div>
        `;
    }).join('');
}

// Iniciar nova batalha
async function startNewBattle() {
    if (warriors.length < 2) return;
    
    // Escolher dois guerreiros aleatórios diferentes
    const idx1 = Math.floor(Math.random() * warriors.length);
    let idx2 = Math.floor(Math.random() * warriors.length);
    while (idx2 === idx1) idx2 = Math.floor(Math.random() * warriors.length);
    
    currentBattle = {
        warrior1: warriors[idx1],
        warrior2: warriors[idx2]
    };
    
    renderBattle(currentBattle.warrior1, currentBattle.warrior2);
}

// Renderizar cards de batalha
function renderBattle(warrior1, warrior2) {
    const card1 = document.getElementById('card1');
    const card2 = document.getElementById('card2');
    
    if (!card1 || !card2) return;
    
    card1.innerHTML = createBattleCard(warrior1);
    card2.innerHTML = createBattleCard(warrior2);
}

// Criar card de batalha
function createBattleCard(warrior) {
    const winRate = warrior.win_rate || 0;
    
    return `
        <div class="card">
            <img src="${warrior.avatar_url}" alt="${warrior.name}">
            <h2>${escapeHtml(warrior.name)}</h2>
            <p class="login">@${warrior.login}</p>
            <div class="battle-stats">
                <div class="stat">
                    <span class="stat-label">⭐ Pontuação</span>
                    <span class="stat-value">${warrior.score}</span>
                </div>
                <div class="stat">
                    <span class="stat-label">🏆 Vitórias</span>
                    <span class="stat-value">${warrior.wins}</span>
                </div>
                <div class="stat">
                    <span class="stat-label">📈 Taxa</span>
                    <span class="stat-value">${winRate}%</span>
                </div>
            </div>
            <div class="github-info">
                <span>📚 ${warrior.public_repos} repos</span>
                <span>👥 ${warrior.followers} followers</span>
            </div>
            <button class="vote-btn" onclick="vote('${warrior.id}', '${currentBattle.warrior1.id === warrior.id ? currentBattle.warrior2.id : currentBattle.warrior1.id}')">
                ⚔️ Escolher ${warrior.name} ⚔️
            </button>
        </div>
    `;
}

// Registrar voto
async function vote(winnerId, loserId) {
    try {
        const response = await fetch('/api/battle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ winner_id: winnerId, loser_id: loserId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`${data.winner.name} venceu! +${data.score_change} pontos!`, 'success');
            await loadRanking(); // Recarregar ranking
            setTimeout(() => startNewBattle(), 1500);
        } else {
            showMessage(data.error, 'error');
        }
    } catch (error) {
        console.error('Erro ao votar:', error);
        showMessage('Erro ao registrar voto', 'error');
    }
}

// Adicionar novo guerreiro
async function addWarrior() {
    const username = document.getElementById('github-username').value.trim();
    
    if (!username) {
        showMessage('Digite um nome de usuário do GitHub', 'error');
        return;
    }
    
    showMessage(`Buscando ${username} no GitHub...`, 'info');
    
    try {
        const response = await fetch('/api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: username })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`✨ ${data.warrior.name} entrou na rinha! ✨`, 'success');
            await loadRanking();
            document.getElementById('github-username').value = '';
            startNewBattle();
        } else {
            showMessage(data.error, 'error');
        }
    } catch (error) {
        console.error('Erro ao adicionar:', error);
        showMessage('Erro ao adicionar guerreiro', 'error');
    }
}

// Mostrar mensagem temporária
function showMessage(message, type) {
    const msgDiv = document.getElementById('game-messages');
    if (!msgDiv) return;
    
    msgDiv.innerHTML = `<div class="message ${type}">${message}</div>`;
    setTimeout(() => {
        msgDiv.innerHTML = '';
    }, 3000);
}

// Utilitário para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-refresh a cada 10 segundos
setInterval(() => {
    loadRanking();
}, 10000);

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    loadRanking();
});

// game.js - Versão simplificada e funcionando

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
    
    if (top10.length === 0) {
        rankingDiv.innerHTML = '<p>Nenhum guerreiro ainda</p>';
        return;
    }
    
    rankingDiv.innerHTML = top10.map((warrior, index) => {
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}`;
        const winRate = warrior.win_rate || 0;
        
        return `
            <div class="ranking-item ${index === 0 ? 'first-place' : ''}">
                <span class="rank-medal">${medal}</span>
                <img src="${warrior.avatar_url}" class="rank-avatar" alt="${warrior.name}" onerror="this.src='https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png'">
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

// Iniciar nova batalha
function startNewBattle() {
    if (warriors.length < 2) return;
    
    const idx1 = Math.floor(Math.random() * warriors.length);
    let idx2 = Math.floor(Math.random() * warriors.length);
    while (idx2 === idx1) idx2 = Math.floor(Math.random() * warriors.length);
    
    currentBattle = {
        warrior1: warriors[idx1],
        warrior2: warriors[idx2]
    };
    
    renderBattle();
}

// Renderizar cards de batalha
function renderBattle() {
    const card1 = document.getElementById('card1');
    const card2 = document.getElementById('card2');
    
    if (!card1 || !card2 || !currentBattle) return;
    
    card1.innerHTML = createBattleCard(currentBattle.warrior1, currentBattle.warrior1.id === currentBattle.warrior1.id);
    card2.innerHTML = createBattleCard(currentBattle.warrior2, currentBattle.warrior2.id === currentBattle.warrior2.id);
}

// Criar card de batalha
function createBattleCard(warrior, isFirst) {
    const opponent = isFirst ? currentBattle.warrior2 : currentBattle.warrior1;
    
    return `
        <div class="card">
            <img src="${warrior.avatar_url}" alt="${warrior.name}" onerror="this.src='https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png'">
            <h2>${escapeHtml(warrior.name)}</h2>
            <p>@${warrior.login}</p>
            <p>⭐ Pontuação: ${warrior.score}</p>
            <p>🏆 Vitórias: ${warrior.wins}</p>
            <p>📚 Repos: ${warrior.public_repos}</p>
            <p>👥 Followers: ${warrior.followers}</p>
            <button onclick="vote('${warrior.id}', '${opponent.id}')">
                🗳️ Escolher ${warrior.name}
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
            showMessage(`${data.winner.name} venceu!`, 'success');
            await loadRanking();
            setTimeout(() => startNewBattle(), 1500);
        } else {
            showMessage(data.error, 'error');
        }
    } catch (error) {
        console.error('Erro ao votar:', error);
        showMessage('Erro ao registrar voto', 'error');
    }
}

// Adicionar guerreiro
async function addWarrior() {
    const username = document.getElementById('github-username').value.trim();
    
    if (!username) {
        showMessage('Digite um nome de usuário', 'error');
        return;
    }
    
    showMessage(`Buscando ${username}...`, 'info');
    
    try {
        const response = await fetch('/api/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: username })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage(`✨ ${data.warrior.name} entrou na rinha! ✨`, 'success');
            document.getElementById('github-username').value = '';
            await loadRanking();
            startNewBattle();
        } else {
            showMessage(data.error, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        showMessage('Erro ao adicionar guerreiro', 'error');
    }
}

// Mostrar mensagem
function showMessage(message, type) {
    const msgDiv = document.getElementById('game-messages');
    if (!msgDiv) return;
    
    msgDiv.innerHTML = `<div class="message ${type}">${message}</div>`;
    setTimeout(() => {
        msgDiv.innerHTML = '';
    }, 3000);
}

// Utilitário
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    loadRanking();
});

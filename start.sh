#!/bin/bash
# start.sh - Script de inicialização para o Render

echo "🚀 Iniciando GitRinha no Render..."

# Criar pasta data se não existir
mkdir -p data

# Inicializar arquivos JSON se não existirem
if [ ! -f data/users.json ]; then
    echo "[]" > data/users.json
fi

if [ ! -f data/battles.json ]; then
    echo "[]" > data/battles.json
fi

if [ ! -f data/scores.json ]; then
    echo "{}" > data/scores.json
fi

echo "✅ Arquivos de dados inicializados"

# Iniciar servidor PHP
echo "🌐 Servidor rodando na porta ${PORT:-10000}"
php -S 0.0.0.0:${PORT:-10000} router.php

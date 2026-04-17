@echo off
title GitRinha Server
echo ========================================
echo    GitRinha - Batalha de Devs
echo ========================================
echo.

REM Criar pasta data se não existir
if not exist "data" (
    echo Criando pasta data...
    mkdir data
    echo Pasta data criada!
)

REM Criar arquivos JSON se não existirem
if not exist "data\users.json" (
    echo [] > data\users.json
    echo Arquivo users.json criado!
)

if not exist "data\battles.json" (
    echo [] > data\battles.json
    echo Arquivo battles.json criado!
)

if not exist "data\scores.json" (
    echo {} > data\scores.json
    echo Arquivo scores.json criado!
)

echo.
echo Iniciando servidor PHP...
echo.
echo Acesse: http://localhost:8000
echo Pressione CTRL+C para parar o servidor
echo.

REM Iniciar servidor PHP
php -S localhost:8000 router.php

<?php
// setup.php - Configuração inicial do projeto

echo "<!DOCTYPE html>
<html>
<head>
    <title>GitRinha - Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        h1 { color: #667eea; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        button:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>⚔️ GitRinha - Setup ⚔️</h1>";

// Criar diretórios
$directories = ['data'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='success'>✅ Diretório '{$dir}' criado com sucesso!</p>";
        } else {
            echo "<p class='error'>❌ Erro ao criar diretório '{$dir}'</p>";
        }
    } else {
        echo "<p class='info'>📁 Diretório '{$dir}' já existe</p>";
    }
}

// Criar arquivos JSON
$files = [
    'data/users.json' => '[]',
    'data/battles.json' => '[]',
    'data/scores.json' => '{}'
];

foreach ($files as $file => $defaultContent) {
    if (!file_exists($file)) {
        if (file_put_contents($file, $defaultContent)) {
            echo "<p class='success'>✅ Arquivo '{$file}' criado com sucesso!</p>";
        } else {
            echo "<p class='error'>❌ Erro ao criar arquivo '{$file}'</p>";
        }
    } else {
        echo "<p class='info'>📄 Arquivo '{$file}' já existe</p>";
    }
}

// Verificar extensões PHP
$extensions = ['curl', 'json'];
echo "<h3>🔍 Verificando extensões PHP:</h3>";
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ Extensão '{$ext}' está carregada</p>";
    } else {
        echo "<p class='error'>❌ Extensão '{$ext}' NÃO está carregada</p>";
    }
}

// Verificar versão do PHP
echo "<h3>📊 Informações do Sistema:</h3>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Built-in server') . "\n";
echo "OS: " . PHP_OS . "\n";
echo "</pre>";

echo "<hr>";
echo "<p class='success'>✅ Setup concluído!</p>";
echo "<button onclick=\"window.location.href='index.php'\">🚀 Iniciar GitRinha</button>";
echo "<button onclick=\"window.location.href='router.php'\" style='margin-left: 10px;'>🔄 Testar Router</button>";

echo "
    </div>
</body>
</html>";
?>

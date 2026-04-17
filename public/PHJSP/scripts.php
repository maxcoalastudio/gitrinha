<?php 
require_once('PARSE.phjsp');

$js = new PHJSP();

// Lista de usuários iniciais
$initialUsers = [
    "UnidayStudio", "Cesio137", "maxcoalastudio", "joelgomes1994",
    "LucasMontano", "filipedeschamps", "cristianoAbudu", "deyvin",
    "akitaonrails", "Rafael-Fagiani", "Bernardo-Ribeiro"
];

$js->function('initwarriors', ['users'], '
    console.log("Inicializando guerreiros...");
    
    fetch("/api/init_warriors", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ users: users })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Resposta:", data);
        if (data.success) {
            console.log(data.total + " guerreiros adicionados");
            if (data.errors) {
                console.log("Erros:", data.errors);
            }
            setTimeout(function() {
                if (typeof loadRanking === "function") {
                    loadRanking();
                }
            }, 2000);
        }
    })
    .catch(error => console.error("Erro ao inicializar:", error));
');

$js->raw('
var initialUsers = ' . json_encode($initialUsers) . ';
initwarriors(initialUsers);
');

echo $js;
?>

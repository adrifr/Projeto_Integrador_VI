
<?php
include('../conexao.php'); // ajuste o caminho conforme onde você salvar

if ($conexao instanceof PDO) {
    echo "✅ Conexão via PDO bem-sucedida!";
} elseif ($conexao instanceof mysqli) {
    echo "✅ Conexão via MySQLi bem-sucedida!";
} else {
    echo "❌ Erro: conexão não estabelecida.";
}
?>
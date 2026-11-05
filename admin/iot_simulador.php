<?php
include('../conexao.php'); // conexão MySQLi

// Quantas leituras de presença gerar por execução
$qtd = 5;

// Loop para gerar dados simulados de presença
for ($i = 0; $i < $qtd; $i++) {

    // Gera um valor aleatório de presença (0 = sem presença, 1 = presença detectada)
    $valor = rand(0, 1);

    // Insere no banco de dados
    $sql = "INSERT INTO iot_dados (tipo_sensor, valor, data_registro)
            VALUES ('Presença', '$valor', NOW())";
    $conn->query($sql);
}

echo "✅ $qtd registros de presença simulados foram inseridos com sucesso!<br>";
echo "⏱️ " . date('d/m/Y H:i:s');
?>
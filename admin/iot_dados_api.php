<?php
include('../conexao.php');

// Total de presenças (valor = 1)
$result = $conn->query("SELECT COUNT(*) AS total FROM iot_dados WHERE tipo_sensor='Presença' AND valor=1");
$total = $result->fetch_assoc()['total'] ?? 0;

// Últimos 20 registros para o gráfico
$res = $conn->query("SELECT valor, data_registro FROM iot_dados WHERE tipo_sensor='Presença' ORDER BY data_registro DESC LIMIT 20");
$dados = [];
while ($row = $res->fetch_assoc()) {
    $dados[] = $row;
}

// Inverte para ordem cronológica
$dados = array_reverse($dados);

$horarios = array_column($dados, 'data_registro');
$valores = array_column($dados, 'valor');

header('Content-Type: application/json');
echo json_encode([
  'total_presencas' => (int)$total,
  'horarios' => $horarios,
  'valores' => $valores
]);
?>
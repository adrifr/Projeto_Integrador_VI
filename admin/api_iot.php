<?php
include('../conexao.php');

$tipo = $_GET['tipo'] ?? '';
$valor = $_GET['valor'] ?? '';

if($tipo && $valor){
    $stmt = $conexao->prepare("INSERT INTO iot_dados (tipo_sensor, valor) VALUES (?, ?)");
    $stmt->execute([$tipo, $valor]);
    echo "✅ Dado recebido com sucesso! Tipo: $tipo | Valor: $valor";
} else {
    echo "❌ Erro: parâmetros ausentes. Use /api_iot.php?tipo=temperatura&valor=25.4";
}
?>

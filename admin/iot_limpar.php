<?php
include('../conexao.php');
$conn->query("DELETE FROM iot_dados WHERE tipo_sensor='Presença'");
echo "✅ Histórico de presença apagado com sucesso!";
?>
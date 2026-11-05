<?php
include('../conexao.php');

$sql = "
SELECT COUNT(*) as total_atual
FROM iot_dados
WHERE tipo_sensor = 'Presença' 
AND valor = 1
AND DATE(data_registro) = CURDATE()
";

$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    echo $row['total_atual'];
}
?>
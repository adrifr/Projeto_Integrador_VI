<?php
include(__DIR__ . '/conexao.php'); // caminho seguro

if(isset($_GET['valor'])) {

    $valor = intval($_GET['valor']);

    if($valor == 1){

        $sql = "INSERT INTO iot_dados (tipo_sensor, valor, data_registro)
                VALUES ('Presença', 1, NOW())";

        if ($conn->query($sql) === TRUE) {
            echo "OK";
        } else {
            echo "Erro SQL: " . $conn->error;
        }

    } else {
        echo "Valor inválido";
    }

} else {
    echo "Sem dados";
}
?>
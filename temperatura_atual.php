<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
try {
    require __DIR__ . '/conexao.php';
    /** @var mysqli $conn */
    $resultado = $conn->query("SELECT valor, data_registro, TIMESTAMPDIFF(SECOND, data_registro, NOW()) AS idade FROM iot_dados WHERE tipo_sensor = 'Temperatura' ORDER BY data_registro DESC, id DESC LIMIT 1");
    if (!$resultado) throw new RuntimeException('query');
    $linha = $resultado->fetch_assoc();
    $dados = ['temperatura' => null, 'atualizado_em' => null, 'atrasada' => true];
    if ($linha) {
        $dados = ['temperatura' => (float) $linha['valor'], 'atualizado_em' => $linha['data_registro'], 'atrasada' => (int) $linha['idade'] > 180 || (int) $linha['idade'] < 0];
    }
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    $conn->close();
} catch (Throwable $e) {
    error_log('Consultar temperatura: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Temperatura indisponivel.']);
}

<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function responder($codigo, $mensagem) {
    http_response_code($codigo);
    echo json_encode(['mensagem' => $mensagem]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    responder(405, 'Use POST para enviar a leitura.');
}
require_once __DIR__ . '/iot_config.php';
$chave = $_SERVER['HTTP_X_IOT_KEY'] ?? '';
if (!hash_equals(IOT_CHAVE, $chave)) responder(401, 'Dispositivo nao autorizado.');
$entrada = $_POST['temperatura'] ?? null;
if (!is_string($entrada) || !is_numeric($entrada)) responder(422, 'Temperatura invalida.');
$temperatura = (float) $entrada;
if (!is_finite($temperatura) || $temperatura < -40 || $temperatura > 80) responder(422, 'Temperatura fora da faixa do sensor.');
try {
    require __DIR__ . '/conexao.php';
    /** @var mysqli $conn */
    $stmt = $conn->prepare("INSERT INTO iot_dados (tipo_sensor, valor, data_registro) VALUES ('Temperatura', ?, NOW())");
    if (!$stmt) throw new RuntimeException('prepare');
    $stmt->bind_param('d', $temperatura);
    if (!$stmt->execute()) throw new RuntimeException('execute');
    $stmt->close();
    $conn->close();
    responder(201, 'Temperatura registrada.');
} catch (Throwable $e) {
    error_log('Receber temperatura: ' . $e->getMessage());
    responder(500, 'Falha ao registrar a temperatura.');
}

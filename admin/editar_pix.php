<?php
include '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $novo_nome = $_POST['nome_evento'];
    $imagem_url = null;

    // Verifica se uma nova imagem foi enviada
    if (!empty($_FILES['nova_imagem']['name'])) {
        $imagem = $_FILES['nova_imagem'];
        $nome_arquivo = 'pix_' . time() . '_' . basename($imagem['name']);
        $caminho_destino = '../uploads/' . $nome_arquivo;

        if (move_uploaded_file($imagem['tmp_name'], $caminho_destino)) {
            // Salva o caminho relativo para o banco de dados
            $imagem_url = 'uploads/' . $nome_arquivo;
        }
    }

    // Atualiza o banco de dados
    if ($imagem_url) {
        $stmt = $conn->prepare("UPDATE pix_eventos SET nome_evento = ?, imagem_pix = ? WHERE id = ?");
        $stmt->bind_param("ssi", $novo_nome, $imagem_url, $id);
    } else {
        $stmt = $conn->prepare("UPDATE pix_eventos SET nome_evento = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_nome, $id);
    }

    if ($stmt->execute()) {
        header("Location: admin_pix.php?editado=1");
        exit;
    } else {
        echo "Erro ao atualizar evento.";
    }
} else {
    echo "Requisição inválida.";
}
?>

<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Verifica se o arquivo foi enviado
if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] === 0) {
    $arquivo = $_FILES['comprovante'];
    $nome_arquivo = uniqid() . "_" . basename($arquivo['name']);
    $caminho_destino = "../uploads/" . $nome_arquivo;

    // Verifica extensão permitida
    $extensao_permitida = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));

    if (!in_array($extensao, $extensao_permitida)) {
        echo "Tipo de arquivo não permitido.";
        exit;
    }

    if (move_uploaded_file($arquivo['tmp_name'], $caminho_destino)) {
        $url_comprovante = "uploads/" . $nome_arquivo;

        // Inserir ou atualizar o pagamento
        $sql = "INSERT INTO pagamentos (user_id, comprovante_url, status)
                VALUES (?, ?, 'feito')
                ON DUPLICATE KEY UPDATE comprovante_url = VALUES(comprovante_url), data_pagamento = NOW(), status = 'feito'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $user_id, $url_comprovante);

        if ($stmt->execute()) {
            header("Location: painel_usuario.php?comprovante=enviado");
        } else {
            echo "Erro ao salvar pagamento: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Erro ao mover o arquivo.";
    }
} else {
    echo "Nenhum arquivo enviado.";
}
?>

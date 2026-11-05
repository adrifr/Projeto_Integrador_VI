<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $post_id = $_POST['id'];

    // Buscar imagem antes de deletar
    $busca = $conn->prepare("SELECT imagem_url FROM blog WHERE id = ?");
    $busca->bind_param("i", $post_id);
    $busca->execute();
    $busca->bind_result($imagem_url);
    $busca->fetch();
    $busca->close();

    // Apagar imagem (se existir)
    if ($imagem_url && file_exists("../" . $imagem_url)) {
        unlink("../" . $imagem_url);
    }

    // Deletar post
    $delete = $conn->prepare("DELETE FROM blog WHERE id = ?");
    $delete->bind_param("i", $post_id);

    if ($delete->execute()) {
        header("Location: painel_admin.php");
    } else {
        echo "Erro ao excluir post: " . $conn->error;
    }

    $delete->close();
    $conn->close();
} else {
    echo "Requisição inválida.";
}
?>

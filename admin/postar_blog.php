<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$titulo = $_POST['titulo'];
$conteudo = $_POST['conteudo'];
$imagem_url = "";

// Se houver imagem enviada
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
    $nome_arquivo = uniqid() . "_" . basename($_FILES['imagem']['name']);
    $caminho = "../uploads/" . $nome_arquivo;
    $ext = strtolower(pathinfo($nome_arquivo, PATHINFO_EXTENSION));
    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $permitidos)) {
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho)) {
            $imagem_url = "uploads/" . $nome_arquivo;
        }
    }
}

// Inserir no banco
$stmt = $conn->prepare("INSERT INTO blog (titulo, conteudo, imagem_url) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $titulo, $conteudo, $imagem_url);

if ($stmt->execute()) {
    header("Location: painel_admin.php");
} else {
    echo "Erro ao postar: " . $conn->error;
}

$stmt->close();
$conn->close();
?>

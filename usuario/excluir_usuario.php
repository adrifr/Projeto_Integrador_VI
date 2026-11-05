<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Excluir membro (tabela members)
    $sql1 = "DELETE FROM members WHERE user_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();

    // Excluir usuário (tabela users)
    $sql2 = "DELETE FROM users WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();

    // Encerrar sessão e redirecionar
    session_destroy();
    header("Location: ../index.php");
    exit;
} else {
    echo "Requisição inválida.";
}
?>

<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Excluir dados do membro
    $stmt1 = $conn->prepare("DELETE FROM members WHERE user_id = ?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $stmt1->close();

    // Excluir usuário
    $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: painel_admin.php");
    exit;
} else {
    echo "Requisição inválida.";
}
?>

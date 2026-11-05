<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $sobrenome = $_POST['sobrenome'];
    $bairro = $_POST['bairro'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $casa = $_POST['casa'];
    $cep = $_POST['cep'];
    $idade = $_POST['idade'];
    $sexo = $_POST['sexo'];

    $sql = "UPDATE members SET nome=?, sobrenome=?, bairro=?, rua=?, numero=?, casa=?, cep=?, idade=?, sexo=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $nome, $sobrenome, $bairro, $rua, $numero, $casa, $cep, $idade, $sexo, $id);

    if ($stmt->execute()) {
        header("Location: painel_usuario.php?atualizado=1");
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acesso inválido.";
}
?>

<?php
session_start();
include 'conexao.php'; // Arquivo com conexão ao banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        $resultado = $stmt->get_result();
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();

            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['tipo'] = $usuario['tipo'];

                if ($usuario['tipo'] === 'admin') {
                    header('Location: admin/painel_admin.php');
                } else {
                    header('Location: usuario/painel_usuario.php');
                }
                exit;
            } else {
                $erro = "Senha incorreta.";
            }
        } else {
            $erro = "Usuário não encontrado.";
        }
    } else {
        $erro = "Erro na execução da consulta.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML da página de login -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>
<body>
  <h2>Login</h2>
  <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>
  <form method="POST" action="">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>
    
    <label>Senha:</label><br>
    <input type="password" name="senha" required><br><br>
    
    <input type="submit" value="Entrar">
  </form>
  <p><a href="cadastro.php">Não tem conta? Cadastre-se aqui</a></p>
</body>
</html>

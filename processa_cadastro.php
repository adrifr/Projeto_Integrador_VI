<?php
// Conexão com banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "igreja_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Conexão falhou: " . $conn->connect_error);
}

// Recebe os dados do formulário
$nome = $_POST['nome'];
$sobrenome = $_POST['sobrenome'];
$bairro = $_POST['bairro'];
$rua = $_POST['rua'];
$numero = $_POST['numero'];
$casa = $_POST['casa'];
$cep = $_POST['cep'];
$idade = $_POST['idade'];
$sexo = $_POST['sexo'];
$email = $_POST['email'];
$senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

// Insere usuário na tabela users
$sqlUser = "INSERT INTO users (email, senha, tipo) VALUES (?, ?, 'usuario')";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("ss", $email, $senha);

if ($stmtUser->execute()) {
  $user_id = $conn->insert_id;

  // Insere membro na tabela members
  $sqlMember = "INSERT INTO members (user_id, nome, sobrenome, bairro, rua, numero, casa, cep, idade, sexo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmtMember = $conn->prepare($sqlMember);
  $stmtMember->bind_param("isssssssis", $user_id, $nome, $sobrenome, $bairro, $rua, $numero, $casa, $cep, $idade, $sexo);

  if ($stmtMember->execute()) {
    echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='index.php';</script>";
  } else {
    echo "Erro ao cadastrar membro: " . $stmtMember->error;
  }
} else {
  echo "Erro ao cadastrar usuário: " . $stmtUser->error;
}

$stmtUser->close();
$conn->close();
?>
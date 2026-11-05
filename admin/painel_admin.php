<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="painel-admin.css?v=1">
  <title>Painel do Administrador</title>
  <style>
    body { font-family: Arial; }
    nav a { margin-right: 15px; text-decoration: none; font-weight: bold; }
    .aba { display: none; padding: 20px; border-top: 1px solid #ccc; }
    .ativa { display: block; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
  </style>
  <script>
    function mostrarAba(abaId) {
      const abas = document.querySelectorAll('.aba');
      abas.forEach(aba => aba.classList.remove('ativa'));
      document.getElementById(abaId).classList.add('ativa');
    }
  </script>
</head>
<body class="painel-admin">
  <h2>Painel do Administrador</h2>
  <nav>
    <a href="#" onclick="mostrarAba('blog')">📢 Blog</a>
    <a href="#" onclick="mostrarAba('membros')">👥 Membros</a>
    <a href="#" onclick="mostrarAba('pagamentos')">💸 Pagamentos</a>
    <a href="admin_pix.php">Pix</a>
    <a href="analise_ibge.php">📊 Análise IBGE</a>
    <a href="iot_dados.php">🌐 Dados IoT</a>
    <div style="text-align: right; margin: 10px;">
  <a href="http://localhost/Projeto_Integrador/logout.php" style="color: #fff; background: #c62828; padding: 8px 12px; border-radius: 5px; text-decoration: none;">Sair</a>
</div>
  </nav>

  <!-- ABA BLOG -->
  <div id="blog" class="aba ativa">
    <h3>Postar novo conteúdo</h3>
    <form method="POST" action="postar_blog.php" enctype="multipart/form-data">
      <label>Título:</label><br>
      <input type="text" name="titulo" required><br><br>
      
      <label>Texto:</label><br>
      <textarea name="conteudo" rows="5" required></textarea><br><br>
      
      <label>Imagem:</label><br>
      <input type="file" name="imagem" accept="image/*"><br><br>
      
      <input type="submit" value="Postar">
    </form>

    <h3>Postagens existentes</h3>
    <?php
    $buscaPosts = $conn->query("SELECT * FROM blog ORDER BY id DESC");
    while ($post = $buscaPosts->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; margin:10px 0; padding:10px;'>";
        echo "<h4>{$post['titulo']}</h4>";
        echo "<p>{$post['conteudo']}</p>";
        if ($post['imagem_url']) {
            echo "<img src='../{$post['imagem_url']}' width='150'><br>";
        }
        echo "<form method='POST' action='excluir_post.php'>";
        echo "<input type='hidden' name='id' value='{$post['id']}'>";
        echo "<button type='submit'>Excluir</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
  </div>

  <!-- ABA MEMBROS -->
  <div id="membros" class="aba">
    <h3>Membros Cadastrados</h3>
    <?php
    $buscaMembros = $conn->query("SELECT * FROM members INNER JOIN users ON members.user_id = users.id");
    echo "<table><tr><th>Nome</th><th>Email</th><th>Bairro</th><th>Ações</th></tr>";
    while ($m = $buscaMembros->fetch_assoc()) {
        echo "<tr>
                <td>{$m['nome']} {$m['sobrenome']}</td>
                <td>{$m['email']}</td>
                <td>{$m['bairro']}</td>
                <td>
                  <form method='POST' action='excluir_membro.php' style='display:inline;'>
                    <input type='hidden' name='user_id' value='{$m['user_id']}'>
                    <button type='submit'>Excluir</button>
                  </form>
                </td>
              </tr>";
    }
    echo "</table>";
    ?>
  </div>

  <!-- ABA PAGAMENTOS -->
  <div id="pagamentos" class="aba">
    <h3>Pagamentos Feitos</h3>
    <?php
    $feitos = $conn->query("SELECT u.email, p.data_pagamento, p.comprovante_url 
                            FROM pagamentos p 
                            INNER JOIN users u ON p.user_id = u.id 
                            WHERE p.status = 'feito'");
    echo "<table><tr><th>Email</th><th>Data</th><th>Comprovante</th></tr>";
    while ($p = $feitos->fetch_assoc()) {
        echo "<tr>
                <td>{$p['email']}</td>
                <td>{$p['data_pagamento']}</td>
                <td><a href='../{$p['comprovante_url']}' target='_blank'>Ver</a></td>
              </tr>";
    }
    echo "</table>";
    ?>
    <br>

    <h3>Pagamentos Não Feitos</h3>
    <?php
    $naoPagaram = $conn->query("SELECT email FROM users 
                                WHERE tipo = 'usuario' AND id NOT IN 
                                (SELECT user_id FROM pagamentos WHERE status = 'feito')");
    echo "<ul>";
    while ($np = $naoPagaram->fetch_assoc()) {
        echo "<li>{$np['email']}</li>";
    }
    echo "</ul>";
    ?>
  </div>
</body>
</html>

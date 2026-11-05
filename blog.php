<?php
include 'conexao.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM blog WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $post = $resultado->fetch_assoc();
    } else {
        die("Postagem não encontrada.");
    }

    $stmt->close();
} else {
    die("ID inválido.");
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['titulo']); ?> - Blog da Igreja</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; margin: 0; padding: 0; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; margin-top: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { color: #4A148C; }
        img { max-width: 100%; height: auto; border-radius: 5px; margin-top: 10px; margin-bottom: 10px; }
        a.voltar { display: inline-block; margin-top: 20px; color: #6A1B9A; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
        <p><em>Publicado em <?php echo date('d/m/Y H:i', strtotime($post['data_postagem'])); ?></em></p>

        <?php if (!empty($post['imagem_url'])): ?>
            <img src="<?php echo htmlspecialchars($post['imagem_url']); ?>" alt="Imagem do post">
        <?php endif; ?>

        <p><?php echo nl2br(htmlspecialchars($post['conteudo'])); ?></p>

        <a href="index.php" class="voltar">← Voltar para o blog</a>
    </div>
</body>
</html>

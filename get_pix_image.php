<?php
include 'conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT imagem_pix FROM pix_eventos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imagem);
    $stmt->fetch();
    $stmt->close();

    if ($imagem && file_exists($imagem)) {
        $extensao = pathinfo($imagem, PATHINFO_EXTENSION);

        // Define o tipo correto de conteúdo
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp'
        ];

        $tipo = $mime_types[strtolower($extensao)] ?? 'application/octet-stream';
        header("Content-Type: $tipo");

        readfile($imagem);
        exit;
    }
}

// Caso não encontre imagem ou ocorra erro
http_response_code(404);
echo "Imagem não encontrada.";
?>


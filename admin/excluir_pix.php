<?php
include '../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Buscar o caminho da imagem antes de excluir o registro
    $query = $conn->prepare("SELECT imagem_pix FROM pix_eventos WHERE id = ?");
    $query->bind_param("i", $id);
    $query->execute();
    $query->bind_result($imagem);
    $query->fetch();
    $query->close();

    // Exclui o registro do banco de dados
    $stmt = $conn->prepare("DELETE FROM pix_eventos WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Remove a imagem do servidor se ela existir
        if ($imagem && file_exists("../" . $imagem)) {
            unlink("../" . $imagem); // Corrigido para acessar a pasta uploads/
        }

        header("Location: admin_pix.php?excluido=1");
        exit;
    } else {
        echo "Erro ao excluir evento.";
    }
} else {
    echo "ID do evento não especificado.";
}
?>

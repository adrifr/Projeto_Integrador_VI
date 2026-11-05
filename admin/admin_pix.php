<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['tipo']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fluxo de cadastro preservado do projeto original.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_evento'])) {
    $nome_evento = $_POST['nome_evento'];
    $imagem_nome = '';
    $caminho_imagem = '';
    if (!empty($_FILES['imagem_pix']['name'])) {
        $imagem_nome = 'pix_' . time() . '_' . $_FILES['imagem_pix']['name'];
        $caminho_imagem = 'uploads/' . $imagem_nome;
        move_uploaded_file($_FILES['imagem_pix']['tmp_name'], '../uploads/' . $imagem_nome);
    }
    $stmt = $conn->prepare('INSERT INTO pix_eventos (nome_evento, imagem_pix) VALUES (?, ?)');
    $stmt->bind_param('ss', $nome_evento, $caminho_imagem);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_pix.php');
    exit;
}
$result = $conn->query('SELECT * FROM pix_eventos ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos Pix | Projeto Integrador</title>
    <link rel="stylesheet" href="painel-admin.css?v=1">
    <style>
        .painel-admin nav a[aria-current="page"] { background: #2c4771; color: #fff; }
        .pix-intro { color: #64748b; margin-top: -14px !important; margin-bottom: 28px !important; }
        .pix-bloco { margin-bottom: 24px !important; }
        .pix-campo { margin-bottom: 20px; }
        .pix-campo label { display: block; }
        .pix-ajuda { color: #64748b; font-size: 14px; margin-top: 8px !important; }
        .pix-lista { display: grid; gap: 20px; }
        .pix-evento { border: 1px solid #e1e7ef; border-radius: 12px; padding: 24px; background: #fcfdff; min-width: 0; }
        .pix-imagem { display: block; width: auto; max-width: 100%; max-height: 320px; height: auto; border: 1px solid #e1e7ef; border-radius: 8px; padding: 8px; background: white; }
        .pix-acoes { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 24px; }
        .painel-admin .pix-excluir { display: inline-block; padding: 10px 16px; min-height: 44px; border: 1px solid #fecdd3; border-radius: 8px; color: #b42334; background: #fff1f2; font-size: 14px; font-weight: 600; text-decoration: none; }
        .pix-sucesso { padding: 14px 18px; background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; border-radius: 10px; }
        .pix-vazio { padding: 24px; border: 1px dashed #cbd5e1; border-radius: 10px; color: #64748b; }
        @media (max-width: 540px) { .pix-evento { padding: 16px; } }
    </style>
</head>
<body class="painel-admin">
    <h2>Eventos Pix</h2>
    <nav aria-label="Menu administrativo">
        <a href="painel_admin.php">← Painel principal</a>
        <a href="admin_pix.php" aria-current="page">Pix</a>
        <a href="analise_ibge.php">📊 Análise IBGE</a>
        <a href="iot_dados.php">🌐 Dados IoT</a>
        <div><a href="../logout.php">Sair</a></div>
    </nav>
    <p class="pix-intro">Cadastre eventos e organize as imagens Pix disponíveis para os membros.</p>

    <?php if (isset($_GET['editado']) && $_GET['editado'] == 1): ?>
        <p class="pix-sucesso" role="status">Evento atualizado com sucesso.</p>
    <?php endif; ?>
    <?php if (isset($_GET['excluido']) && $_GET['excluido'] == 1): ?>
        <p class="pix-sucesso" role="status">Evento excluído com sucesso.</p>
    <?php endif; ?>

    <section class="aba ativa pix-bloco" aria-labelledby="novo-evento">
        <h3 id="novo-evento">Adicionar novo evento</h3>
        <form action="admin_pix.php" method="POST" enctype="multipart/form-data">
            <div class="pix-campo">
                <label for="nome-evento">Nome do evento</label>
                <input id="nome-evento" type="text" name="nome_evento" placeholder="Ex.: Aniversário da igreja" required>
            </div>
            <div class="pix-campo">
                <label for="imagem-pix">Imagem Pix</label>
                <input id="imagem-pix" type="file" name="imagem_pix" accept="image/*" required aria-describedby="ajuda-imagem">
                <p id="ajuda-imagem" class="pix-ajuda">Selecione a imagem do QR Code que será exibida aos membros.</p>
            </div>
            <button type="submit">Cadastrar evento</button>
        </form>
    </section>

    <section class="aba ativa" aria-labelledby="eventos-cadastrados">
        <h3 id="eventos-cadastrados">Eventos cadastrados</h3>
        <?php if ($result->num_rows > 0): ?>
            <div class="pix-lista">
            <?php while ($row = $result->fetch_assoc()): $id = (int) $row['id']; ?>
                <article class="pix-evento">
                    <form action="editar_pix.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <div class="pix-campo">
                            <label for="evento-<?= $id ?>">Nome do evento</label>
                            <input id="evento-<?= $id ?>" type="text" name="nome_evento" value="<?= htmlspecialchars($row['nome_evento'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class="pix-campo">
                            <p><strong>Imagem atual</strong></p>
                            <?php if (!empty($row['imagem_pix'])): ?>
                                <img class="pix-imagem" src="../<?= htmlspecialchars($row['imagem_pix'], ENT_QUOTES, 'UTF-8') ?>" alt="Imagem Pix do evento <?= htmlspecialchars($row['nome_evento'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <p class="pix-ajuda">Este evento não possui imagem cadastrada.</p>
                            <?php endif; ?>
                        </div>
                        <div class="pix-campo">
                            <label for="nova-imagem-<?= $id ?>">Nova imagem (opcional)</label>
                            <input id="nova-imagem-<?= $id ?>" type="file" name="nova_imagem" accept="image/*">
                            <p class="pix-ajuda">Deixe este campo vazio para manter a imagem atual.</p>
                        </div>
                        <div class="pix-acoes">
                            <button type="submit">Salvar alterações</button>
                            <a class="pix-excluir" href="excluir_pix.php?id=<?= $id ?>" onclick="return confirm('Tem certeza que deseja excluir este evento?')">Excluir evento</a>
                        </div>
                    </form>
                </article>
            <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="pix-vazio">Nenhum evento cadastrado. Use o formulário acima para adicionar o primeiro.</p>
        <?php endif; ?>
    </section>
</body>
</html>

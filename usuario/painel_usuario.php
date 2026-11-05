<?php
session_start();
include '../conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['tipo'] !== 'usuario') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar dados do membro
$sql = "SELECT * FROM members WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_assoc();
?>

<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Painel do Usuário | Projeto Integrador</title><style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;color:#183344;background:#f3f6f9;line-height:1.5}
.topo{background:#003b46;color:white}.topo-inner{max-width:960px;margin:auto;padding:22px 28px;display:flex;align-items:center;justify-content:space-between;gap:20px}.marca{font-size:14px;font-weight:700;letter-spacing:1.4px}.topo a{color:white;font-size:14px;text-decoration:none;border:1px solid #74949b;padding:9px 15px;border-radius:8px}.topo a:hover{background:#15525d}
main{max-width:960px;margin:36px auto;padding:0 28px 40px}.intro{margin-bottom:25px}.etiqueta{font-size:12px;font-weight:700;letter-spacing:1.6px;color:#176776;margin:0 0 7px}h1{font-size:30px;line-height:1.2;margin:0 0 12px;letter-spacing:-.6px}.intro p:last-child{color:#596c7a;margin:0}
.cartao{background:white;border:1px solid #dee7ec;border-radius:16px;padding:30px;box-shadow:0 6px 24px #163b4710}fieldset{border:0;padding:0;margin:0 0 28px;min-width:0}fieldset+fieldset{border-top:1px solid #e5ebef;padding-top:24px}legend{font-size:19px;font-weight:700;padding:0 0 18px;float:left;width:100%}.grade{display:grid;grid-template-columns:1fr 1fr;gap:20px;clear:both}.campo{min-width:0}.campo label{display:block;font-size:14px;font-weight:600;margin-bottom:7px}.campo input,.campo select{display:block;width:100%;min-height:46px;padding:10px 12px;border:1px solid #c7d5df;border-radius:8px;background:#fff;color:#183344;font:inherit;margin:0}.campo input:focus,.campo select:focus{outline:3px solid #0e82952b;border-color:#147b8b}a:focus-visible,button:focus-visible{outline:3px solid #279eb1;outline-offset:4px}.acoes{border-top:1px solid #e5ebef;padding-top:24px;display:flex;align-items:center;justify-content:space-between;gap:20px}.acoes a{color:#176776;font-size:14px}.acoes button{background:#006879;color:white;border:0;padding:13px 30px;font:700 15px Arial,sans-serif;border-radius:8px;cursor:pointer;min-height:46px}.acoes button:hover{background:#004d5a}.nota{color:#647682;font-size:13px;margin:0 0 24px}
@media(max-width:600px){.topo-inner{padding:18px 20px;flex-wrap:wrap}main{margin:26px auto;padding:0 16px 28px}h1{font-size:26px}.cartao{padding:22px 18px}.grade{grid-template-columns:1fr;gap:17px}.acoes{flex-direction:column-reverse;align-items:stretch;text-align:center}.acoes button{width:100%}}

body{padding-left:244px}.menu{position:fixed;inset:0 auto 0 0;width:244px;background:#15263f;color:#fff;padding:34px 20px;display:flex;flex-direction:column;gap:12px}.menu .marca{padding:0 0 25px;border-bottom:1px solid #ffffff26;margin-bottom:15px}.menu a{display:block;color:#eef4ff;text-decoration:none;padding:13px 15px;border-radius:8px;font-size:15px}.menu a:hover,.menu a[aria-current]{background:#293e5b}.menu .sair{margin-top:20px;background:#293e5b}main{max-width:1100px}.cartao{margin-bottom:24px;scroll-margin-top:24px}h2{font-size:22px;margin:0 0 8px}.descricao{color:#647682;margin:0 0 24px}button{border:0;border-radius:8px;padding:13px 22px;background:#2457cd;color:white;font:700 14px Arial;cursor:pointer;min-height:46px}.acoes button{background:#2457cd}.etiqueta{color:#2457cd}.campo{margin-bottom:0}.pagamento .campo{margin:20px 0}.mensagem-sucesso{background:#e6f5ed;color:#205a3e;border:1px solid #b7ddc8;padding:16px;border-radius:10px;margin-bottom:24px}.exclusao{display:flex;align-items:center;justify-content:space-between;gap:20px}.exclusao p{margin:0;color:#647682}.botao-excluir{background:white;color:#b42c39;border:1px solid #dfb4b9;white-space:nowrap}#imagem_pix:empty{display:none}#imagem_pix{margin:20px 0;padding:20px;border:1px solid #dee7ec;border-radius:12px;background:#f8fafc}#imagem_pix img{display:block;max-width:100%;height:auto;background:white}.vazio{color:#647682;padding:16px;background:#f3f6f9;border-radius:8px}
@media(max-width:800px){body{padding-left:0}.menu{position:static;width:auto;padding:20px;flex-direction:row;flex-wrap:wrap;gap:8px}.menu .marca{width:100%;padding-bottom:16px;margin:0 0 8px}.menu a{padding:10px 12px}.menu .sair{margin:0}.exclusao{align-items:flex-start;flex-direction:column}}
</style><script>function confirmarExclusao(){return confirm("Tem certeza que deseja excluir seu cadastro?");}</script></head><body>
<nav class="menu" aria-label="Menu do usuário"><span class="marca">PROJETO INTEGRADOR</span><a href="../index.php">Página inicial</a><a href="#meus-dados">Meus dados</a><a href="#pagamentos">Pagamentos e Pix</a><a class="sair" href="../logout.php">Sair</a></nav>
<main><div class="intro"><p class="etiqueta">ÁREA DO MEMBRO</p><h1>Bem-vindo ao seu painel</h1><p>Atualize seus dados e envie seus comprovantes de pagamento.</p></div>
<?php if (isset($_GET['comprovante']) && $_GET['comprovante'] === 'enviado'): ?><div class="mensagem-sucesso" role="status">Comprovante enviado com sucesso!</div><?php endif; ?>
<section class="cartao" id="meus-dados" aria-labelledby="titulo-dados"><h2 id="titulo-dados">Meus dados</h2><p class="descricao">Mantenha seu cadastro atualizado.</p><form method="POST" action="atualizar_usuario.php"><input type="hidden" name="id" value="<?= $dados['id'] ?>">
<fieldset><legend>Dados pessoais</legend><div class="grade"><div class="campo"><label for="nome">Nome:</label><input type="text" id="nome" name="nome" value="<?= htmlspecialchars((string) ($dados['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div><div class="campo"><label for="sobrenome">Sobrenome:</label><input type="text" id="sobrenome" name="sobrenome" value="<?= htmlspecialchars((string) ($dados['sobrenome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div><div class="campo"><label for="idade">Idade:</label><input type="number" id="idade" name="idade" value="<?= htmlspecialchars((string) ($dados['idade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div><div class="campo"><label for="sexo">Sexo:</label><select id="sexo" name="sexo">
      <option value="Masculino" <?= $dados['sexo'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
      <option value="Feminino" <?= $dados['sexo'] == 'Feminino' ? 'selected' : '' ?>>Feminino</option>
    </select></div></div></fieldset><fieldset><legend>Endereço</legend><div class="grade"><div class="campo"><label for="bairro">Bairro:</label><input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars((string) ($dados['bairro'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div><div class="campo"><label for="rua">Rua:</label><input type="text" id="rua" name="rua" value="<?= htmlspecialchars((string) ($dados['rua'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div><div class="campo"><label for="numero">Número:</label><input type="text" id="numero" name="numero" value="<?= htmlspecialchars((string) ($dados['numero'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div><div class="campo"><label for="casa">Casa:</label><input type="text" id="casa" name="casa" value="<?= htmlspecialchars((string) ($dados['casa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div><div class="campo"><label for="cep">CEP:</label><input type="text" id="cep" name="cep" value="<?= htmlspecialchars((string) ($dados['cep'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div></div></fieldset><div class="acoes"><button type="submit">Atualizar dados</button></div></form></section>
<section class="cartao pagamento" id="pagamentos" aria-labelledby="titulo-pix"><h2 id="titulo-pix">Dízimos e pagamentos</h2><p class="descricao">Selecione o evento, escaneie o QR Code e envie a imagem do comprovante.</p><?php
// Conexão já carregada no início da página.

$eventos = $conn->query("SELECT * FROM pix_eventos ORDER BY id DESC");

if ($eventos->num_rows > 0): ?>
    <form action="enviar_comprovante.php" method="POST" enctype="multipart/form-data">
        <div class="campo"><label for="evento">Evento:</label>
        <select name="evento_id" id="evento" required>
            <option value="">-- Selecione --</option>
            <?php while($ev = $eventos->fetch_assoc()): ?>
                <option value="<?php echo $ev['id']; ?>"><?php echo htmlspecialchars($ev['nome_evento']); ?></option>
            <?php endwhile; ?>
        </select></div>
        

        <div id="imagem_pix"></div>

        <div class="campo"><label for="comprovante">Comprovante de pagamento:</label>
        <input type="file" id="comprovante" name="comprovante" accept="image/*" required></div>
        

        <button type="submit">Enviar Comprovante</button>
    </form>

    <script>
    document.getElementById("evento").addEventListener("change", function() {
    const id = this.value;
    const container = document.getElementById("imagem_pix");
    container.innerHTML = "";

    if (id) {
        const img = document.createElement("img");
        img.src = "../get_pix_image.php?id=" + id;
        img.style.width = "240px";
        img.alt = "QR Code Pix do evento selecionado";
        img.style.marginTop = "10px";
        container.appendChild(img);
    }
});

    </script>

<?php else: ?>
  <p class="vazio"><strong>⏰ Sem eventos no momento.</strong></p>
<?php endif; ?>


</section>
<section class="cartao exclusao" aria-labelledby="titulo-conta"><div><h2 id="titulo-conta">Gerenciar cadastro</h2><p>Use esta opção se desejar excluir seu cadastro.</p></div><form method="POST" action="excluir_usuario.php" onsubmit="return confirmarExclusao();"><input type="hidden" name="user_id" value="<?= $user_id ?>"><button type="submit" class="botao-excluir">Excluir cadastro</button></form></section>
</main></body></html>
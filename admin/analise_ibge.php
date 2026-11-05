<?php
// ✅ Carrega o autoloader do Composer (necessário para usar PhpSpreadsheet)
require_once __DIR__ . '/../vendor/autoload.php';

// ✅ Importa a classe IOFactory para leitura de arquivos Excel
use PhpOffice\PhpSpreadsheet\IOFactory;

// ✅ Conecta ao banco de dados (caso precise salvar dados depois)
include('../conexao.php');

// Caminho do arquivo Excel local (fallback, caso a API falhe)
$arquivoLocal = __DIR__ . '/../data/estimativas_ibge.xls';

// Função para ler o Excel local com PhpSpreadsheet
function lerArquivoExcel($arquivoLocal) {
    if (!file_exists($arquivoLocal)) {
        return [];
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($arquivoLocal);
    } catch (Exception $e) {
        die("Erro ao carregar planilha IBGE: " . $e->getMessage());
    }

    $sheet = $spreadsheet->getSheetByName('MUNICÍPIOS');
    if (!$sheet) {
        $sheet = $spreadsheet->getSheetByName('MUNICIPIOS');
    }

    $dados = [];
    $rows = $sheet->toArray(null, true, true, true);

    foreach ($rows as $index => $row) {
        if ($index <= 1) continue;

        $codigoMunicipio = trim($row['C']); // COD. MUNIC
        $nome = trim($row['D']); // NOME DO MUNICIPIO
        $valorBruto = preg_replace('/[^\d]/', '', $row['E']); // remove tudo que não for número
        $populacao = (int) $valorBruto;
        if (!empty($codigoMunicipio) && $populacao > 0) {
            $dados[] = [
                'codigo' => ltrim($codigoMunicipio, '0'),
                'nome' => $nome,
                'populacao' => $populacao
            ];
        }
    }

    return $dados;
}

// Função para tentar buscar os dados da API do IBGE
function buscarDadosAPI($municipioID) {
    $tabela = 6579;  // estimativas populacionais
    $variavel = 93;  // população residente
    $url = "https://servicodados.ibge.gov.br/api/v3/agregados/{$tabela}/periodos/last/variaveis/{$variavel}?localidades=N6[{$municipioID}]";

    $context = stream_context_create(['http' => ['timeout' => 10]]);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) return false;

    $json = json_decode($response, true);
    if (!isset($json[0]['resultados'][0]['series'][0]['serie'])) return false;

    $serie = $json[0]['resultados'][0]['series'][0]['serie'];
    $ano = array_key_first($serie);

    return [
        'ano' => $ano,
        'populacao' => $serie[$ano]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Análise IBGE Municipal</title>
<link rel="stylesheet" href="painel-admin.css?v=1">
<style>
.painel-admin nav a[aria-current="page"] { background: #2c4771; color: #fff; }
.painel-admin .ibge-card { margin-bottom: 24px; }
.painel-admin .ibge-descricao { color: #64748b; margin-bottom: 24px; }
.painel-admin .ibge-busca { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.painel-admin .ibge-busca input { flex: 1 1 260px; min-width: 0; }
.painel-admin .ibge-acoes { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
.painel-admin #municipiosInput { min-height: 100px; max-width: none; background: #f8fafc; }
.painel-admin #adicionarBtn { background: #eaf0fc; color: #2458c6; border-color: #cbd9f7; }
.painel-admin #limparBtn { background: white; color: #475569; border-color: #cbd5e1; }
.painel-admin hr { border: 0; border-top: 1px solid #e1e7ef; margin: 28px 0; }
.painel-admin .ibge-grafico-topo { display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center; }
.painel-admin .ibge-grafico-topo > div { display: flex; flex-wrap: wrap; gap: 10px; }
.painel-admin .ibge-grafico-topo h4 { margin: 0; }
.painel-admin .ibge-grafico-area { position: relative; height: 400px; width: 100%; margin: 24px 0; }
.painel-admin .info-box { background: #f3f6fb; border: 1px solid #e1e7ef; padding: 22px; border-radius: 12px; margin-top: 24px; }
.painel-admin .info-box small { display: block; color: #64748b; }
.painel-admin .info-box p:last-child { margin-bottom: 0; }
@media(max-width: 540px) {
  .painel-admin .ibge-grafico-area { height: 320px; }
  .painel-admin .ibge-acoes button { flex: 1 1 auto; }
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="painel-admin">

<h2>Análise IBGE</h2>
<nav aria-label="Menu administrativo">
  <a href="painel_admin.php">← Painel principal</a>
  <a href="admin_pix.php">Pix</a>
  <a href="analise_ibge.php" aria-current="page">📊 Análise IBGE</a>
  <a href="iot_dados.php">🌐 Dados IoT</a>
  <div><a href="../logout.php">Sair</a></div>
</nav>

<div class="container aba ativa ibge-card">
  <h3>📊 População Municipal — IBGE</h3>
  <p class="ibge-descricao">Digite o nome de um ou mais municípios e clique em <b>Gerar Gráfico</b>.</p>

  <?php
  // 🔹 Carrega municípios do Excel
  $dadosLocal = lerArquivoExcel($arquivoLocal);
  $nomesMunicipios = array_column($dadosLocal, 'nome');
  sort($nomesMunicipios);
  ?>

  <form id="formMunicipios" method="GET">
    <label for="buscaMunicipio">Buscar município</label>
    <div class="ibge-busca">
    <input list="listaMunicipios" id="buscaMunicipio" placeholder="Digite o nome do município..." autocomplete="off">
    <datalist id="listaMunicipios">
      <?php foreach ($nomesMunicipios as $nome): ?>
        <option value="<?= htmlspecialchars($nome) ?>">
      <?php endforeach; ?>
    </datalist>

    <button type="button" id="adicionarBtn">Adicionar</button>
    </div>
    <label for="municipiosInput">Municípios selecionados</label>
    <textarea id="municipiosInput" name="municipios" rows="3" cols="60" readonly placeholder="Municípios selecionados aparecerão aqui..."></textarea>
    <div class="ibge-acoes">
      <button type="submit" id="plotBtn">Gerar gráfico</button>
      <button type="button" id="limparBtn">Limpar resultados</button>
    </div>
  </form>

  <hr>

 <?php
if (isset($_GET['municipios']) && trim($_GET['municipios']) !== ''):
    $input = $_GET['municipios'];
    $nomesDigitados = array_map('trim', explode(',', $input));
    $resultados = [];

    foreach ($nomesDigitados as $nome) {
        $municipioEncontrado = array_filter($dadosLocal, function($m) use ($nome) {
            return mb_strtolower(trim($m['nome'])) === mb_strtolower(trim($nome));
        });

        if ($municipioEncontrado) {
            $m = array_values($municipioEncontrado)[0];

            // Mantemos o valor real em habitantes para o gráfico
            $populacaoGrafico = (int) $m['populacao'];

            // Texto exibido para o usuário (com unidade)
            if (mb_strtolower($m['nome']) === 'são paulo') {
                $populacaoTexto = round($populacaoGrafico / 1000000, 2); // em milhões (ex: 12.33)
                $unidade = 'milhões';
            } else {
                $populacaoTexto = $populacaoGrafico;
                $unidade = 'habitantes';
            }

            $resultados[] = [
                'nome' => $m['nome'],
                'populacao' => $populacaoGrafico, // valor numérico absoluto para o gráfico
                'texto' => $populacaoTexto,       // valor para exibição (pode ser milhões)
                'unidade' => $unidade,
                'fonte' => 'Arquivo local IBGE (XLS)',
                'ano' => '2024'
            ];
        }
    }

    if (count($resultados) > 0):
?>
<!-- HTML do gráfico e botões -->
<div class="ibge-grafico-topo">
  <h4>📈 Gráfico de População</h4>
  <div>
    <button id="exportarPDF">Exportar PDF</button>
    <button id="exportarIMG">Salvar Imagem</button>
  </div>
</div>

<div class="ibge-grafico-area"><canvas id="graficoIBGE" role="img" aria-label="Gráfico de população dos municípios selecionados"></canvas></div>

<script src="https://cdn.jsdelivr.net/npm/jspdf"></script>
<script>
const ctx = document.getElementById('graficoIBGE');
const labels = <?= json_encode(array_column($resultados, 'nome')) ?>;
const dataVals = <?= json_encode(array_column($resultados, 'populacao')) ?>;
const unidades = <?= json_encode(array_column($resultados, 'unidade')) ?>;
const textos = <?= json_encode(array_column($resultados, 'texto')) ?>;

const grafico = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'População Estimada (2024)',
      data: dataVals,
      backgroundColor: labels.map((_, i) => ['#2458c6', '#168579', '#8060b5', '#b46b22', '#437fa8'][i % 5]),
      borderRadius: 6
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        suggestedMax: Math.max(...dataVals) * 1.2,
        ticks: {
          callback: (value) => {
            const max = Math.max(...dataVals);
            if (max > 1000000) {
              return (value / 1000000).toFixed(1) + ' mi';
            } else if (max > 1000) {
              return (value / 1000).toFixed(0) + ' mil';
            } else {
              return value.toLocaleString('pt-BR');
            }
          }
        }
      }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => {
            const unidade = unidades[ctx.dataIndex];
            const valorTexto = textos[ctx.dataIndex];
            if (unidade === 'milhões') {
              return ctx.label + ': ' + (valorTexto).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) + ' ' + unidade;
            } else {
              return ctx.label + ': ' + Number(valorTexto).toLocaleString('pt-BR') + ' ' + unidade;
            }
          }
        }
      }
    }
  }
});

// Exportar imagem
document.getElementById('exportarIMG').addEventListener('click', () => {
  const link = document.createElement('a');
  link.download = 'grafico_ibge.png';
  link.href = grafico.toBase64Image();
  link.click();
});

// Exportar PDF
document.getElementById('exportarPDF').addEventListener('click', () => {
  const pdf = new jsPDF();
  pdf.text('Gráfico de População IBGE (2024)', 10, 10);
  pdf.addImage(grafico.toBase64Image(), 'PNG', 10, 20, 180, 100);
  pdf.save('grafico_ibge.pdf');
});
</script>

      <div class="info-box">
  <h4>📋 Resultados</h4>
  <?php foreach ($resultados as $res): ?>
    <?php
      // Garante que todas as chaves existam
      $nome = htmlspecialchars($res['nome'] ?? '—');
      $texto = isset($res['texto']) ? $res['texto'] : ($res['populacao'] ?? 0);
      $unidade = htmlspecialchars($res['unidade'] ?? 'habitantes');
      $fonte = htmlspecialchars($res['fonte'] ?? 'IBGE');
    ?>
    <p><b><?= $nome ?>:</b>
       <?= number_format($texto, ($unidade === 'milhões' ? 2 : 0), ',', '.') . ' ' . $unidade ?>
       <small>(<?= $fonte ?>)</small>
    </p>
  <?php endforeach; ?>
</div>
  <?php
      else:
          echo "<p style='color:red;'>❌ Nenhum município encontrado.</p>";
      endif;
  endif;
  ?>

  <script>
  document.getElementById('adicionarBtn').addEventListener('click', () => {
      const campoBusca = document.getElementById('buscaMunicipio');
      const campoLista = document.getElementById('municipiosInput');
      const nome = campoBusca.value.trim();

      if (nome !== '') {
          let lista = campoLista.value.trim();
          if (lista !== '') lista += ', ';
          campoLista.value = lista + nome;
          campoBusca.value = '';
      }
  });

  document.getElementById('limparBtn').addEventListener('click', () => {
      document.getElementById('buscaMunicipio').value = '';
      document.getElementById('municipiosInput').value = '';
      window.location.href = window.location.pathname;
  });

  document.getElementById('formMunicipios').addEventListener('submit', e => {
      const txt = document.getElementById('municipiosInput').value.trim();
      if (txt === '') {
          alert('Por favor, adicione pelo menos um município antes de gerar o gráfico.');
          e.preventDefault();
      }
  });
  </script>
</div>
</body>
</html>

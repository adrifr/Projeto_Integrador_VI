<?php
//include('../conexao.php');
require_once __DIR__ . '/../conexao.php';

// 🔹 Filtro de datas
$data_inicio = $_GET['inicio'] ?? date('Y-m-01');
$data_fim = $_GET['fim'] ?? date('Y-m-d');

// 🔹 Consulta com filtro
$sql = "
SELECT DATE(data_registro) as data, COUNT(*) as total
FROM iot_dados
WHERE tipo_sensor = 'Presença' 
AND valor = 1
AND DATE(data_registro) BETWEEN '$data_inicio' AND '$data_fim'
GROUP BY DATE(data_registro)
ORDER BY DATE(data_registro) ASC
";

$result = $conn->query($sql);

$datas = [];
$totais = [];
$total_geral = 0;

while ($row = $result->fetch_assoc()) {
    $datas[] = date('d/m/Y', strtotime($row['data']));
    $totais[] = (int)$row['total'];
    $total_geral += $row['total'];
}

// 🔹 Último valor (tempo real - culto atual)
$sqlAtual = "
SELECT COUNT(*) as total_atual
FROM iot_dados
WHERE tipo_sensor = 'Presença' 
AND valor = 1
AND DATE(data_registro) = CURDATE()
";

$resultAtual = $conn->query($sqlAtual);
$total_culto = 0;

if ($rowAtual = $resultAtual->fetch_assoc()) {
    $total_culto = $rowAtual['total_atual'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard IoT</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="painel-admin.css?v=1">
<style>
.painel-admin nav a[aria-current="page"] { background:#2c4771; color:#fff; }
.painel-admin .container { max-width:1280px; margin:0 auto; }
.painel-admin .iot-intro { color:#64748b; margin:-12px 0 28px; }
.painel-admin .cards { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:20px; margin-bottom:24px; }
.painel-admin .card { background:#fff; padding:24px; border:1px solid #e1e7ef; border-radius:14px; min-width:0; }
.painel-admin .card h3 { font-size:15px; color:#64748b; margin:0; letter-spacing:0; }
.painel-admin .card p { font-size:36px; font-weight:700; color:#2458c6; margin:12px 0 4px; }
.painel-admin .card small { color:#64748b; font-size:13px; }
.painel-admin .card.destaque { background:#eaf0fc; border-color:#cbd9f7; }
.painel-admin .filtro { margin-bottom:24px; }
.painel-admin .filtro form { display:flex; flex-wrap:wrap; gap:18px; align-items:end; }
.painel-admin .filtro .campo-data { flex:1 1 180px; min-width:0; }
.painel-admin .campo-data label { display:block; margin-top:0; }
.painel-admin .grafico-area { position:relative; width:100%; height:380px; }
.painel-admin .iot-vazio { border:1px dashed #cbd5e1; border-radius:8px; padding:14px 18px; color:#64748b; background:#f8fafc; }
@media(max-width:1100px) { .painel-admin .cards { gap:12px; } .painel-admin .card { padding:18px; } }
@media(max-width:650px) { .painel-admin .cards { grid-template-columns:1fr; } .painel-admin .grafico-area { height:300px; } }
</style>
</head>

<body class="painel-admin">
<h2>Dados IoT</h2>
<nav aria-label="Menu administrativo">
  <a href="painel_admin.php">← Painel principal</a>
  <a href="admin_pix.php">Pix</a>
  <a href="analise_ibge.php">📊 Análise IBGE</a>
  <a href="iot_dados.php" aria-current="page">🌐 Dados IoT</a>
  <div><a href="../logout.php">Sair</a></div>
</nav>
<p class="iot-intro">Acompanhe os registros de presença e consulte o histórico por período.</p>

<div class="container">

  <!-- 🔹 Cards -->
  <div class="cards">

  <div class="card">
    <h3>Total no período</h3>
    <p><?= $total_geral ?></p>
    <small>Registros nas datas selecionadas</small>
  </div>

  <!-- ✅ NOVO CARD -->
  <div class="card destaque">
    <h3>Registros de hoje</h3>
    <p id="totalCulto"><?= $total_culto ?></p>
    <small>Atualização automática a cada 5 segundos</small>
  </div>

  <div class="card">
    <h3>Dias com registros</h3>
    <p><?= count($datas) ?></p>
    <small>Dentro do período selecionado</small>
  </div>

</div>

  <!-- 🔹 Filtro -->
  <div class="filtro aba ativa">
    <h3>Filtrar período</h3>
    <form method="GET">
      <div class="campo-data">
        <label for="inicio">Data inicial</label>
        <input id="inicio" type="date" name="inicio" value="<?= htmlspecialchars($data_inicio, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="campo-data">
        <label for="fim">Data final</label>
        <input id="fim" type="date" name="fim" value="<?= htmlspecialchars($data_fim, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <button type="submit">Filtrar</button>
    </form>
  </div>

  <!-- 🔹 Gráfico -->
  <section class="aba ativa" aria-labelledby="titulo-grafico">
    <h3 id="titulo-grafico">Registros de presença por dia</h3>
    <?php if (count($datas) === 0): ?>
      <p class="iot-vazio">Nenhum registro encontrado no período selecionado.</p>
    <?php endif; ?>
    <div class="grafico-area"><canvas id="grafico" role="img" aria-label="Gráfico de registros de presença por dia"></canvas></div>
  </section>

</div>

<script>
const ctx = document.getElementById('grafico');

const grafico = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($datas) ?>,
    datasets: [{
      label: 'Registros por dia',
      data: <?= json_encode($totais) ?>,
      backgroundColor: '#2458c6',
      borderRadius: 5
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: true
      }
    },
    scales: {
      x: {
        ticks: {
          color: '#333'
        }
      },
      y: {
        beginAtZero: true,
        ticks: {
          precision: 0
        }
      }
    }
  }
});
</script>

<script>
function atualizarContador() {
  fetch('tempo_real.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById("totalCulto").innerText = data;
    });
}

// Atualiza a cada 5 segundos
setInterval(atualizarContador, 5000);
</script>

</body>
</html>

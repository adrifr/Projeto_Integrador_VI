<?php
include('../conexao.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Monitoramento de Presença IoT</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; background: #f9f9f9; margin: 20px; }
h2 { color: #333; }
nav a { margin-right: 15px; text-decoration: none; font-weight: bold; }
.container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
.counter { font-size: 2em; font-weight: bold; color: #2b83ba; margin: 15px 0; }
button { padding: 8px 12px; background: #c62828; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
button:hover { background: #a82020; }
canvas { width: 100% !important; height: 400px !important; margin-top: 20px; }
</style>
</head>
<body>

<h2>Painel do Administrador</h2>
<nav>
  <a href="painel_admin.php">🏠 Início</a>
  <a href="analise_ibge.php">📊 Análise IBGE</a>
  <a href="iot_dados.php">🌐 Dados IoT</a>
  <a href="admin_pix.php">💸 Pix</a>
  <a href="../logout.php" style="color:#fff;background:#c62828;padding:8px 12px;border-radius:5px;text-decoration:none;">Sair</a>
</nav>
<hr>

<div class="container">
  <h3>🧍‍♂️ Contador de Presença — Tempo Real</h3>
  <p>Cada vez que o sensor detecta uma presença (valor = 1), o contador aumenta.</p>

  <div class="counter" id="contador">Carregando...</div>
  
  <button id="limparBtn">🧹 Limpar Histórico</button>

  <canvas id="graficoPresenca"></canvas>
</div>

<script>
const ctx = document.getElementById('graficoPresenca');
let grafico;

// Função para buscar dados da API
async function atualizarDados() {
  const response = await fetch('iot_dados_api.php');
  const dados = await response.json();

  document.getElementById('contador').innerText = dados.total_presencas + ' pessoas detectadas';

  // Atualiza o gráfico
  if (!grafico) {
    grafico = new Chart(ctx, {
      type: 'line',
      data: {
        labels: dados.horarios,
        datasets: [{
          label: 'Detecções de presença',
          data: dados.valores,
          borderColor: '#2b83ba',
          borderWidth: 2,
          fill: false,
          tension: 0.3
        }]
      },
      options: {
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { display: false } }
      }
    });
  } else {
    grafico.data.labels = dados.horarios;
    grafico.data.datasets[0].data = dados.valores;
    grafico.update();
  }
}

// Atualiza a cada 5 segundos
setInterval(atualizarDados, 5000);
atualizarDados();

// Limpar histórico
document.getElementById('limparBtn').addEventListener('click', async () => {
  if (confirm('Deseja realmente limpar o histórico de presença?')) {
    const response = await fetch('iot_limpar.php');
    const texto = await response.text();
    alert(texto);
    atualizarDados();
  }
});
</script>

</body>
</html>
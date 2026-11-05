const munId = 354580; // exemplo: Salto/SP
const tabela = 6579;  // estimativas populacionais (agregado)
const variavel = 93;  // população residente total
const url = `https://servicodados.ibge.gov.br/api/v3/agregados/${tabela}/periodos/last/variaveis/${variavel}?localidades=N6[${munId}]`;

fetch(url)
  .then(resp => {
    if (!resp.ok) {
      throw new Error(`HTTP status ${resp.status}`);
    }
    return resp.json();
  })
  .then(json => {
    console.log("Resposta JSON:", json);
    // extrair dados conforme estrutura: json[0].resultados[0].series[0].serie
    const serie = json[0].resultados[0].series[0].serie;
    const ano = Object.keys(serie)[0];
    const valor = serie[ano];
    console.log(`População estimada (${ano}):`, valor);
  })
  .catch(err => {
    console.error("Falha na API do IBGE:", err);
  });
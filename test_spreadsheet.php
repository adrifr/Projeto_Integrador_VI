<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$arquivo = 'data/estimativas_ibge.xls';

try {
    $spreadsheet = IOFactory::load($arquivo);
    $sheet = $spreadsheet->getActiveSheet();
    echo "✅ PhpSpreadsheet está funcionando!**<br>";
    echo "Primeira célula: " . $sheet->getCell('A1')->getValue();
} catch (Exception $e) {
    echo "❌ Erro ao carregar planilha: " . $e->getMessage();
}
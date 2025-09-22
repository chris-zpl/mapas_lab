<?php
require '../../src/PHPExcel/Classes/PHPExcel.php';

// Recebe os dados JSON
$input = json_decode(file_get_contents('php://input'), true);
$dados = $input['dados'] ? $input['dados'] : [];
$sala = $input['nomeSalaFormatado'] ? $input['nomeSalaFormatado'] : [];
$predio = $input['predioFormatado'] ? $input['predioFormatado'] : [];

// Se não houverem dados, responde com código 400 e redireciona para "pagina-nao-encontrada"
if (empty($dados)) {
    http_response_code(400);
    header('Location: pagina-nao-encontrada');
    exit;
}

// Cria planilha
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet()->setTitle($predio . " " . $sala);

// Opcionais: 
// Ativar a proteção da planilha para que o desbloqueio das células tenha efeito
$sheet->getProtection()->setSheet(true);
// Remove linhas de grade
$sheet->setShowGridlines(false);
// Deixa um tamanho fico para coluna A
$sheet->getColumnDimension('A')->setWidth(3);
// Define autor
$objPHPExcel->getProperties()->setCreator("PUCRS - Escola Politécnica");

// Preenche com dados
foreach ($dados as $linha => $registro) {
    foreach ($registro as $coluna => $valor) {
        $col = PHPExcel_Cell::stringFromColumnIndex($coluna + 1);
        $row = $linha + 2;
        $cellCoordinate = $col . $row;

        $sheet->setCellValue($cellCoordinate, $valor);

        // Quebrar texto automaticamente
        $sheet->getStyle($cellCoordinate)->getAlignment()->setWrapText(true);

        // Centralizar horizontal e verticalmente
        $sheet->getStyle($cellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($cellCoordinate)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        // Desbloquear célula para edição
        $sheet->getStyle($cellCoordinate)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
    }
}
//Define estilo para o cabeçalho
$estiloCabecalho = [
    'fill' => [
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => ['rgb' => 'e6e6e6']
    ],
    'font' => [
        'bold' => true,
    ],
];

// Define estilo de bordas
$estiloBorda = [
    'borders' => [
        'allborders' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ],
];
// Define o estilo da primeira linha
$estiloPrimeiraColuna = [
    'font' => [
        'bold' => true,
    ],
];
// Faz a contagem das linhas e colunas
$totalLinhas = count($dados) + 1;
$totalColunas = count($dados[0] ? $dados[0] : []);
$inicioColuna = PHPExcel_Cell::stringFromColumnIndex(1); // B
$ultimaColuna = PHPExcel_Cell::stringFromColumnIndex($totalColunas);

// Aplica o estilo no cabeçalho
$sheet->getStyle("{$inicioColuna}2:{$ultimaColuna}2")->applyFromArray($estiloCabecalho);
// Aplica o estilo com bordas
$sheet->getStyle("{$inicioColuna}2:{$ultimaColuna}{$totalLinhas}")->applyFromArray($estiloBorda);
// Aplica o estilo na primeira coluna
$sheet->getStyle("{$inicioColuna}2:{$inicioColuna}{$totalLinhas}")->applyFromArray($estiloPrimeiraColuna);

// Ajusta largura
for ($i = 0; $i < $totalColunas; $i++) {
    $letra = PHPExcel_Cell::stringFromColumnIndex($i + 1);
    $sheet->getColumnDimension($letra)->setAutoSize(true);
}

// Atribui o nome do arquivo. OBS: O nome do arquivo já está sendo atribuído no front
$filename = 'tabela_' . date('Y-m-d') . '.xlsx';

// Limpa o buffer para evitar erro de arquivo corrompido
if (ob_get_length()) {
    ob_end_clean();
}

// Cabeçalhos para download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Gera XLSX
$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$writer->save('php://output');
exit;

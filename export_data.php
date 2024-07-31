<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'vendor/autoload.php'; // Certifique-se de ter o autoload do Composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

// Verifique se os parâmetros necessários foram enviados
if (!isset($_GET['username']) || !isset($_GET['month']) || !isset($_GET['year']) || !isset($_GET['export_type'])) {
    die('Parâmetros insuficientes para exportar a folha de ponto.');
}

$username = $_GET['username'];
$month = intval($_GET['month']);
$year = intval($_GET['year']);
$exportType = $_GET['export_type']; // 'csv', 'xlsx' ou 'pdf'

// Função para obter dados de ponto do usuário
function getUserTimeRecords($username, $month, $year) {
    $file = "data/$username.json"; // Arquivo de registros de tempo do usuário
    if (!file_exists($file)) {
        return [[], []];
    }
    $records = json_decode(file_get_contents($file), true);

    // Verificar se o arquivo contém registros
    if (!isset($records) || !is_array($records) || !isset($records['profile'])) {
        return [[], []];
    }

    // Remover a chave 'profile' para obter apenas os registros de ponto
    $profile = $records['profile'];
    unset($records['profile']);

    $userRecords = [];

    foreach ($records as $date => $record) {
        if (isset($record['date'])) {
            $recordDate = DateTime::createFromFormat('Y-m-d', $record['date']);
        } else {
            $recordDate = DateTime::createFromFormat('Y-m-d', $date);
        }

        // Verificar se a data foi criada corretamente
        if ($recordDate && $recordDate->format('Y') == $year && $recordDate->format('m') == $month) {
            $userRecords[] = array_merge(['date' => $date], $record);
        }
    }

    return [$userRecords, $profile];
}

list($records, $profile) = getUserTimeRecords($username, $month, $year);

// Obter o nome completo do funcionário
$employeeName = isset($profile['name']) ? $profile['name'] : 'Nome do Funcionário';

// Array para meses em português
$monthsInPortuguese = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
];

// Obter o nome do mês por extenso em português
$monthName = isset($monthsInPortuguese[$month]) ? $monthsInPortuguese[$month] : 'Mês Desconhecido';

// Gerar arquivo CSV
function generateCsv($username, $month, $year, $records, $employeeName, $monthName) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="folha_ponto_' . $username . '_' . $month . '_' . $year . '.csv"');

    $output = fopen('php://output', 'w');

    // Adicionar informações no cabeçalho
    fputcsv($output, ["Relatório de Folha de Ponto"]);
    fputcsv($output, ["Mês: " . $monthName]);
    fputcsv($output, ["Ano: " . $year]);
    fputcsv($output, ["Funcionário: " . $employeeName]);

    // Adicionar cabeçalho dos dados
    fputcsv($output, ['Data', 'Hora de Entrada', 'Início do Intervalo', 'Fim do Intervalo', 'Hora de Saída', 'Observação']);

    // Adicionar os dados
    foreach ($records as $record) {
        fputcsv($output, [
            $record['date'] ?? '',
            $record['entry_time'] ?? '',
            $record['break_start'] ?? '',
            $record['break_end'] ?? '',
            $record['exit_time'] ?? '',
            $record['observation'] ?? ''
        ]);
    }

    fclose($output);
}

// Gerar arquivo XLSX
function generateXlsx($username, $month, $year, $records, $employeeName, $monthName) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Adicionar informações no cabeçalho
    $sheet->setCellValue('A1', "Relatório de Folha de Ponto");
    $sheet->setCellValue('A2', "Mês: " . $monthName);
    $sheet->setCellValue('A3', "Ano: " . $year);
    $sheet->setCellValue('A4', "Funcionário: " . $employeeName);

    // Adicionar cabeçalho dos dados
    $sheet->setCellValue('A6', 'Data');
    $sheet->setCellValue('B6', 'Hora de Entrada');
    $sheet->setCellValue('C6', 'Início do Intervalo');
    $sheet->setCellValue('D6', 'Fim do Intervalo');
    $sheet->setCellValue('E6', 'Hora de Saída');
    $sheet->setCellValue('F6', 'Observação');

    // Adicionar os dados
    $row = 7;
    foreach ($records as $record) {
        $sheet->setCellValue('A' . $row, $record['date'] ?? '');
        $sheet->setCellValue('B' . $row, $record['entry_time'] ?? '');
        $sheet->setCellValue('C' . $row, $record['break_start'] ?? '');
        $sheet->setCellValue('D' . $row, $record['break_end'] ?? '');
        $sheet->setCellValue('E' . $row, $record['exit_time'] ?? '');
        $sheet->setCellValue('F' . $row, $record['observation'] ?? '');
        $row++;
    }

    // Definir cabeçalhos para download do arquivo Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="folha_ponto_' . $username . '_' . $month . '_' . $year . '.xlsx"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
}

// Gerar arquivo PDF
function generatePdf($username, $month, $year, $records, $employeeName, $monthName) {
    $dompdfOptions = new Options();
    $dompdfOptions->set('defaultFont', 'Helvetica');
    $dompdf = new Dompdf($dompdfOptions);

    $html = '<h1>Relatório de Folha de Ponto</h1>';
    $html .= '<p>Mês: ' . $monthName . '</p>';
    $html .= '<p>Ano: ' . $year . '</p>';
    $html .= '<p>Funcionário: ' . $employeeName . '</p>';
    $html .= '<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead><tr><th>Data</th><th>Hora de Entrada</th><th>Início do Intervalo</th><th>Fim do Intervalo</th><th>Hora de Saída</th><th>Observação</th></tr></thead>';
    $html .= '<tbody>';

    foreach ($records as $record) {
        $html .= '<tr>';
        $html .= '<td>' . ($record['date'] ?? '') . '</td>';
        $html .= '<td>' . ($record['entry_time'] ?? '') . '</td>';
        $html .= '<td>' . ($record['break_start'] ?? '') . '</td>';
        $html .= '<td>' . ($record['break_end'] ?? '') . '</td>';
        $html .= '<td>' . ($record['exit_time'] ?? '') . '</td>';
        $html .= '<td>' . ($record['observation'] ?? '') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Definir cabeçalhos para download do arquivo PDF
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="folha_ponto_' . $username . '_' . $month . '_' . $year . '.pdf"');

    $dompdf->stream();
}

// Verificar o tipo de exportação
switch ($exportType) {
    case 'csv':
        generateCsv($username, $month, $year, $records, $employeeName, $monthName);
        break;
    case 'xlsx':
        generateXlsx($username, $month, $year, $records, $employeeName, $monthName);
        break;
    case 'pdf':
        generatePdf($username, $month, $year, $records, $employeeName, $monthName);
        break;
    default:
        die('Tipo de exportação desconhecido.');
}
?>

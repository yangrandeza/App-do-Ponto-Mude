<?php
include_once('functions.php');

if (!isset($_GET['username'])) {
    echo json_encode(['error' => 'Usuário não especificado']);
    exit();
}

$username = $_GET['username'];
$data = getUserAttendanceData($username);

if (empty($data)) {
    echo json_encode(['error' => 'Nenhum dado encontrado para o usuário']);
    exit();
}

$days = [];
$hoursWorked = [];
$absences = 0;
$daysWorked = 0;

foreach ($data as $date => $record) {
    if ($date === 'profile') continue;
    $days[] = $date;
    if (!empty($record['entry_time']) && !empty($record['exit_time'])) {
        $entry = new DateTime($record['entry_time']);
        $exit = new DateTime($record['exit_time']);
        $breakStart = new DateTime($record['break_start']);
        $breakEnd = new DateTime($record['break_end']);
        
        $workDuration = $exit->diff($entry)->h - $breakEnd->diff($breakStart)->h;
        $hoursWorked[] = $workDuration;
        $daysWorked++;
    } else {
        $absences++;
    }
}

$response = [
    'days' => $days,
    'hoursWorked' => $hoursWorked,
    'daysWorked' => $daysWorked,
    'absences' => $absences
];

echo json_encode($response);
?>

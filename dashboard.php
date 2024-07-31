<?php
session_start();

$pageTitle = 'Registrar Ponto';
include('header.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Função para salvar o registro de ponto
function saveRecord($username, $date, $entry_time, $break_start, $break_end, $exit_time, $observation) {
    $file = "data/$username.json";
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    $data[$date] = [
        'entry_time' => $entry_time,
        'break_start' => $break_start,
        'break_end' => $break_end,
        'exit_time' => $exit_time,
        'observation' => $observation
    ];

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Processar registro de ponto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $date = $_POST['date'];
    $entry_time = $_POST['entry_time'];
    $break_start = $_POST['break_start'];
    $break_end = $_POST['break_end'];
    $exit_time = $_POST['exit_time'];
    $observation = $_POST['observation']; // Captura a observação

    saveRecord($_SESSION['username'], $date, $entry_time, $break_start, $break_end, $exit_time, $observation);
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ponto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .btn-primary {
            background-color: #820086;
            --bs-btn-bg: #820086;
            --bs-btn-border-color: #820086;
            --bs-btn-hover-bg: #370039;
            --bs-btn-hover-border-color: #370039;
            --bs-btn-active-bg: #820086;
            --bs-btn-active-border-color: #820086;
         }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Registrar Ponto</h2>

        <!-- Formulário para registro de ponto -->
        <form method="post">
            <div class="mb-3">
                <label for="date" class="form-label">Data</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="entry_time" class="form-label">Hora de Entrada</label>
                <input type="time" class="form-control" id="entry_time" name="entry_time" value="08:00" required>
            </div>
            <div class="mb-3">
                <label for="break_start" class="form-label">Início do Intervalo</label>
                <input type="time" class="form-control" id="break_start" name="break_start" value="12:00" required>
            </div>
            <div class="mb-3">
                <label for="break_end" class="form-label">Fim do Intervalo</label>
                <input type="time" class="form-control" id="break_end" name="break_end" value="13:15" required>
            </div>
            <div class="mb-3">
                <label for="exit_time" class="form-label">Hora de Saída</label>
                <input type="time" class="form-control" id="exit_time" name="exit_time" value="18:00" required>
            </div>
            <div class="mb-3">
                <label for="observation" class="form-label">Observação</label>
                <textarea class="form-control" id="observation" name="observation" rows="3"></textarea>
            </div>
            <button type="submit" name="register" class="btn btn-primary">Registrar</button>
        </form>
    </div>
</body>
</html>

<?php include('footer.php');?>

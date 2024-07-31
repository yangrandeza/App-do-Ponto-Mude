<?php
session_start();

$pageTitle = 'Visualizar Registros';
include('header.php');

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

// Definir o fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Obter o mês e ano atuais
$currentMonth = date('m');
$currentYear = date('Y');

// Função para obter os registros de ponto do usuário
function getRecordsByMonth($username, $month, $year) {
  $file = "data/$username.json";
  if (!file_exists($file)) {
    return [];
  }

  $data = json_decode(file_get_contents($file), true);
  $records = [];

  foreach ($data as $date => $entry) {
    $entryDate = DateTime::createFromFormat('Y-m-d', $date);
    if ($entryDate && $entryDate->format('Y') == $year && $entryDate->format('m') == $month) {
      $records[] = [
        'date' => $date,
        'entry_time' => $entry['entry_time'],
        'break_start' => $entry['break_start'],
        'break_end' => $entry['break_end'],
        'exit_time' => $entry['exit_time'],
        'observation' => isset($entry['observation']) ? $entry['observation'] : ''
      ];
    }
  }

  return $records;
}

// Função para atualizar o registro de ponto
function updateRecord($username, $date, $entry_time, $break_start, $break_end, $exit_time, $observation) {
  $file = "data/$username.json";
  if (!file_exists($file)) {
    return;
  }

  $data = json_decode(file_get_contents($file), true);

  if (isset($data[$date])) {
    $data[$date] = [
      'entry_time' => $entry_time,
      'break_start' => $break_start,
      'break_end' => $break_end,
      'exit_time' => $exit_time,
      'observation' => $observation
    ];

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
  }
}

// Função para carregar usuários
function loadUsers() {
  $file = "data/users.json";
  if (file_exists($file)) {
    return json_decode(file_get_contents($file), true);
  }
  return [];
}

// Verificar se o usuário é administrador
$isAdmin = isset($_SESSION['username']) && loadUsers()[$_SESSION['username']]['role'] === 'admin';

// Processar atualização de registro de ponto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
  $date = $_POST['date'];
  $entry_time = $_POST['entry_time'];
  $break_start = $_POST['break_start'];
  $break_end = $_POST['break_end'];
  $exit_time = $_POST['exit_time'];
  $observation = $_POST['observation']; // Captura a observação

  // Verificar se o registro pode ser editado
  $recordDate = DateTime::createFromFormat('Y-m-d', $date);
  if ($recordDate && $recordDate->diff(new DateTime())->days <= 15) {
    updateRecord($_SESSION['username'], $date, $entry_time, $break_start, $break_end, $exit_time, $observation);
  }
}

// Processar visualização de registros
$selectedMonth = isset($_POST['month']) ? str_pad($_POST['month'], 2, '0', STR_PAD_LEFT) : $currentMonth;
$selectedYear = isset($_POST['year']) ? $_POST['year'] : $currentYear;
$selectedUser = isset($_POST['user']) && $isAdmin ? $_POST['user'] : $_SESSION['username'];

$records = getRecordsByMonth($selectedUser, $selectedMonth, $selectedYear);
$users = $isAdmin ? loadUsers() : [];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualizar Registros</title>
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
    <h2>Visualizar Registros</h2>

    <!-- Formulário para seleção de mês e ano -->
    <form method="post" class="mb-4">
      <?php if ($isAdmin): ?>
        <div class="mb-3">
          <label for="user" class="form-label">Usuário</label>
          <select class="form-control" id="user" name="user" required>
            <?php foreach ($users as $username => $user): ?>
              <option value="<?php echo htmlspecialchars($username, ENT_QUOTES); ?>" <?php echo $selectedUser == $username ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($username, ENT_QUOTES); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>
      <div class="mb-3">
        <label for="month" class="form-label">Mês</label>
        <select class="form-control" id="month" name="month" required>
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php echo $selectedMonth == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>>
              <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="year" class="form-label">Ano</label>
        <input type="number" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($selectedYear, ENT_QUOTES); ?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Mostrar Registros</button>
    </form>

    <!-- Tabela de registros -->
    <?php if (!empty($records)): ?>
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Data</th>
            <th>Entrada</th>
            <th>Início do Intervalo</th>
            <th>Fim do Intervalo</th>
            <th>Saída</th>
            <th>Observação</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $record): ?>
            <tr>
              <td><?php echo htmlspecialchars($record['date'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($record['entry_time'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($record['break_start'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($record['break_end'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($record['exit_time'], ENT_QUOTES); ?></td>
              <td><?php echo htmlspecialchars($record['observation'], ENT_QUOTES); ?></td>
              <td>
                <?php
                $recordDate = DateTime::createFromFormat('Y-m-d', $record['date']);
                if ($recordDate && $recordDate->diff(new DateTime())->days <= 15): ?>
                  <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" data-date="<?php echo htmlspecialchars($record['date'], ENT_QUOTES); ?>" data-entry_time="<?php echo htmlspecialchars($record['entry_time'], ENT_QUOTES); ?>" data-break_start="<?php echo htmlspecialchars($record['break_start'], ENT_QUOTES); ?>" data-break_end="<?php echo htmlspecialchars($record['break_end'], ENT_QUOTES); ?>" data-exit_time="<?php echo htmlspecialchars($record['exit_time'], ENT_QUOTES); ?>" data-observation="<?php echo htmlspecialchars($record['observation'], ENT_QUOTES); ?>">Editar</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-info mt-3">
        Nenhum registro encontrado para o mês e ano selecionados.
      </div>
    <?php endif; ?>

    <!-- Modal de edição -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar Registro</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form method="post">
            <div class="modal-body">
              <input type="hidden" id="edit_date" name="date">
              <div class="mb-3">
                <label for="edit_entry_time" class="form-label">Hora de Entrada</label>
                <input type="time" class="form-control" id="edit_entry_time" name="entry_time" required>
              </div>
              <div class="mb-3">
                <label for="edit_break_start" class="form-label">Início do Intervalo</label>
                <input type="time" class="form-control" id="edit_break_start" name="break_start" required>
              </div>
              <div class="mb-3">
                <label for="edit_break_end" class="form-label">Fim do Intervalo</label>
                <input type="time" class="form-control" id="edit_break_end" name="break_end" required>
              </div>
              <div class="mb-3">
                <label for="edit_exit_time" class="form-label">Hora de Saída</label>
                <input type="time" class="form-control" id="edit_exit_time" name="exit_time" required>
              </div>
              <div class="mb-3">
                <label for="edit_observation" class="form-label">Observação</label>
                <textarea class="form-control" id="edit_observation" name="observation"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" name="update" class="btn btn-primary">Atualizar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Script para preencher o modal de edição -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      var editModal = document.getElementById('editModal');
      editModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var date = button.getAttribute('data-date');
        var entry_time = button.getAttribute('data-entry_time');
        var break_start = button.getAttribute('data-break_start');
        var break_end = button.getAttribute('data-break_end');
        var exit_time = button.getAttribute('data-exit_time');
        var observation = button.getAttribute('data-observation');

        var modalDate = editModal.querySelector('#edit_date');
        var modalEntryTime = editModal.querySelector('#edit_entry_time');
        var modalBreakStart = editModal.querySelector('#edit_break_start');
        var modalBreakEnd = editModal.querySelector('#edit_break_end');
        var modalExitTime = editModal.querySelector('#edit_exit_time');
        var modalObservation = editModal.querySelector('#edit_observation');

        modalDate.value = date;
        modalEntryTime.value = entry_time;
        modalBreakStart.value = break_start;
        modalBreakEnd.value = break_end;
        modalExitTime.value = exit_time;
        modalObservation.value = observation;
      });
    </script>
  </div>
</body>
</html>

<?php include('footer.php');?>
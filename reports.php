<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

include_once('functions.php');

// Verifique se o usuário está logado
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Relatórios';
include('header.php');

// Função para obter todos os usuários
function getAllUsers() {
    $file = "data/users.json"; // Arquivo de usuários
    if (!file_exists($file)) {
        return [];
    }
    $users = json_decode(file_get_contents($file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [];
    }
    $userList = [];
    foreach ($users as $username => $user) {
        $userData = getUserData($username); // Função para obter os dados do perfil do usuário
        if (!empty($userData)) {
            $userList[] = [
                'username' => $username,
                'name' => $userData['name'] ?? $username,
                'profile_picture' => $userData['profile_picture'] ?? 'default.jpg'
            ];
        }
    }
    return $userList;
}

$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .profile-card {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            margin-bottom: 15px;
        }
        .profile-card img {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
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
    <div class="container mt-5">
        <h2>Gerar Relatório</h2>
        <input type="text" id="search-user" class="form-control mb-3" placeholder="Buscar usuário...">

        <div id="user-cards" class="row">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="col-md-4 profile-card" data-username="<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" data-name="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>">
                        <img src="<?php echo htmlspecialchars($user['profile_picture'], ENT_QUOTES); ?>" alt="Foto de Perfil">
                        <h4><?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?></h4>
                        <button class="btn btn-primary generate-report" data-username="<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>">Gerar Relatório</button>
                        <button class="btn btn-warning export-points" data-username="<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" data-bs-toggle="modal" data-bs-target="#exportModal_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>">Exportar Folha de Ponto</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nenhum usuário encontrado.</p>
            <?php endif; ?>
        </div>

        <!-- Modal para exibir o relatório -->
        <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Relatório de <span id="report-username"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <canvas id="hoursWorkedChart"></canvas>
                        <canvas id="daysWorkedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para exportar folha de ponto -->
        <?php foreach ($users as $user): ?>
            <div class="modal fade" id="exportModal_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" tabindex="-1" aria-labelledby="exportModalLabel_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportModalLabel_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>">Exportar Folha de Ponto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form action="export_data.php" method="get">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>">
                                <div class="mb-3">
                                    <label for="month_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" class="form-label">Mês</label>
                                    <input type="number" class="form-control" id="month_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" name="month" min="1" max="12" required>
                                </div>
                                <div class="mb-3">
                                    <label for="year_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" class="form-label">Ano</label>
                                    <input type="number" class="form-control" id="year_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" name="year" min="2000" required>
                                </div>
                                <div class="mb-3">
                                    <label for="export_type_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" class="form-label">Formato</label>
                                    <select class="form-select" id="export_type_<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>" name="export_type" required>
                                        <option value="csv">CSV</option>
                                        <option value="xlsx">XLSX</option>
                                        <option value="pdf">PDF</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Exportar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
    document.getElementById('search-user').addEventListener('input', function() {
        const searchValue = this.value.toLowerCase();
        document.querySelectorAll('.profile-card').forEach(card => {
            const username = card.getAttribute('data-username').toLowerCase();
            const name = card.getAttribute('data-name').toLowerCase();
            if (username.includes(searchValue) || name.includes(searchValue)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.generate-report').forEach(button => {
        button.addEventListener('click', function() {
            const username = this.getAttribute('data-username');
            fetch(`get_report_data.php?username=${username}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Erro ao obter dados: ' + data.error);
                        return;
                    }
                    document.getElementById('report-username').innerText = username;
                    generateCharts(data);
                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                })
                .catch(error => {
                    alert('Erro ao buscar dados do relatório');
                    console.error('Error fetching report data:', error);
                });
        });
    });

    function generateCharts(data) {
        const ctxHours = document.getElementById('hoursWorkedChart').getContext('2d');
        const ctxDays = document.getElementById('daysWorkedChart').getContext('2d');

        // Destroy existing charts if they exist to avoid overlapping
        if (window.hoursChart) {
            window.hoursChart.destroy();
        }
        if (window.daysChart) {
            window.daysChart.destroy();
        }

        window.hoursChart = new Chart(ctxHours, {
            type: 'bar',
            data: {
                labels: data.days,
                datasets: [{
                    label: 'Horas Trabalhadas por Dia',
                    data: data.hoursWorked,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        window.daysChart = new Chart(ctxDays, {
            type: 'pie',
            data: {
                labels: ['Faltas', 'Presenças'],
                datasets: [{
                    label: 'Faltas vs Presenças',
                    data: [data.absences, data.daysWorked],
                    backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                    borderColor: ['rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)'],
                    borderWidth: 1
                }]
            }
        });
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php include('footer.php'); ?>

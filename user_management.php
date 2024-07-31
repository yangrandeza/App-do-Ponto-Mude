<?php
session_start();

$pageTitle = 'Dashboard';
include('header.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Função para obter todos os usuários
function getAllUsers() {
    $file = "data/users.json";
    if (!file_exists($file)) {
        return [];
    }
    return json_decode(file_get_contents($file), true);
}

// Função para salvar os usuários
function saveUsers($users) {
    $file = "data/users.json";
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

// Função para criar um arquivo JSON para um novo usuário
function createUserJsonFile($username) {
    $file = "data/$username.json";
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([])); // Cria um JSON vazio para o novo usuário
    }
}

// Processar adição de usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($username && $password) {
        $users = getAllUsers();
        if (!isset($users[$username])) {
            $users[$username] = [
                'password' => $password,
                'role' => $role
            ];
            saveUsers($users);
            createUserJsonFile($username); // Cria o JSON do usuário
            $message = "Usuário adicionado com sucesso!";
        } else {
            $message = "O usuário já existe!";
        }
    } else {
        $message = "Nome de usuário e senha são obrigatórios!";
    }
}

// Processar exclusão de usuário
if (isset($_GET['delete'])) {
    $username = $_GET['delete'];

    if ($username && $username !== $_SESSION['username']) {
        $users = getAllUsers();
        if (isset($users[$username])) {
            unset($users[$username]);
            saveUsers($users);
            unlink("data/$username.json"); // Remove o JSON do usuário excluído
            $message = "Usuário excluído com sucesso!";
        } else {
            $message = "Usuário não encontrado!";
        }
    } else {
        $message = "Você não pode excluir sua própria conta!";
    }
}

// Processar atualização do tipo de usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $username = $_POST['username'];
    $role = $_POST['role'];

    if ($username && $role) {
        $users = getAllUsers();
        if (isset($users[$username])) {
            $users[$username]['role'] = $role;
            saveUsers($users);
            $message = "Tipo de usuário atualizado com sucesso!";
        } else {
            $message = "Usuário não encontrado!";
        }
    } else {
        $message = "Nome de usuário e tipo de usuário são obrigatórios!";
    }
}

$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Usuários</title>
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
        <h2 class="mt-5">Gerenciamento de Usuários</h2>

        <?php if (isset($message)): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Formulário para adicionar novos usuários -->
        <form method="post" class="mb-4">
            <div class="mb-3">
                <label for="username" class="form-label">Nome de Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Tipo de Usuário</label>
                <select class="form-select" id="role" name="role">
                    <option value="user">Usuário</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Adicionar Usuário</button>
        </form>

        <!-- Lista de usuários -->
        <h2>Usuários Cadastrados</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome de Usuário</th>
                    <th>Tipo de Usuário</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $username => $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($username); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php if ($username !== $_SESSION['username']): ?>
                                <a href="?delete=<?php echo urlencode($username); ?>" class="btn btn-danger btn-sm">Excluir</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" class="d-inline" style="display: inline;">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                                <select class="form-select form-select-sm" name="role">
                                    <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>Usuário</option>
                                    <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                </select>
                                <button type="submit" name="update_role" class="btn btn-warning btn-sm mt-2">Atualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php include('footer.php'); ?>
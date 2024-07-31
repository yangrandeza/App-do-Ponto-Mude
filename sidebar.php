<?php
include_once('functions.php');

// Verificar se o usuário é admin e obter dados do perfil
$isAdmin = isAdmin($_SESSION['username']);
$profileData = getUserData($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Lateral</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #f8f9fa;
            padding-top: 20px;
            border-right: 1px solid #dee2e6;
        }
        .sidebar-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .sidebar-logo img {
            max-width: 80%;
            height: auto;
        }
        .sidebar .nav-link {
            font-size: 18px;
            color: #333;
        }
        .sidebar .nav-link:hover {
            background-color: #e9ecef;
        }
        .sidebar .btn-logout {
            position: absolute;
            bottom: 20px;
            left: 20px;
            width: calc(100% - 40px);
        }

        .bymude {
            max-width: 80%;
            height: auto;
            display: flex;
            bottom: 20px;
            left: 20px;
        }
        .profile-info {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 20px;
            margin-left: 10px;
            margin-right: 10px;
            border-bottom: 1px solid #dee2e6;
            background-color: #fff; /* Cor de fundo branca para o card */
            border-radius: 8px; /* Bordas arredondadas */
        }
        .profile-info img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        .profile-info .greeting {
            font-size: 16px;
            color: #333;
        }
        .profile-info .btn-customize {
            display: inline-block;
            margin-top: 10px;
            background-color: #ffc107; /* Cor amarela */
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .profile-info .btn-customize:hover {
            background-color: #e0a800; /* Cor amarela escura ao passar o mouse */
        }
        .profile-info.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo text-center">
            <a href="dashboard.php"><img src="logo.png" alt="Logo"></a>
        </div>
        <div class="profile-info">
            <?php if (!empty($profileData['profile_picture']) && file_exists($profileData['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($profileData['profile_picture'], ENT_QUOTES); ?>" alt="Foto de Perfil">
            <?php else: ?>
                <img src="default-profile.jpg" alt="Foto de Perfil"> <!-- Adicione um avatar padrão caso não haja foto -->
            <?php endif; ?>
            <?php if (!empty($profileData['name'])): ?>
                <div class="greeting">
                    Olá novamente, <?php echo htmlspecialchars($profileData['name'], ENT_QUOTES); ?>
                </div>
            <?php else: ?>
                <a href="profile.php" class="btn-customize">Personalizar Perfil</a>
            <?php endif; ?>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Registrar pontos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_records.php">Visualizar Registros</a>
            </li>
            <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">Relatórios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user_management.php">Gerenciamento de Usuários</a>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">Meu Perfil</a>
            </li>
            <!-- Adicione mais itens de menu conforme necessário -->
        </ul>
        <a href="logout.php" class="btn btn-danger btn-logout">Sair</a>
    </div>
</body>
</html>

<?php
session_start();

$pageTitle = 'Dashboard';
include('header.php');
include_once('functions.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Função para salvar os dados do perfil do usuário
function saveUserData($username, $data) {
    $file = "data/$username.json";
    if (!file_exists($file)) {
        return;
    }
    $currentData = json_decode(file_get_contents($file), true);
    $currentData['profile'] = $data;
    file_put_contents($file, json_encode($currentData, JSON_PRETTY_PRINT));
}

$profileData = getUserData($_SESSION['username']);
$updateSuccess = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Processar o upload da foto de perfil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $uploadFile = $uploadDir . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadFile)) {
            $profilePicture = $uploadFile;
        } else {
            $profilePicture = $profileData['profile_picture'] ?? '';
        }
    } else {
        $profilePicture = $profileData['profile_picture'] ?? '';
    }

    $profileData = [
        'name' => $name,
        'phone' => $phone,
        'address' => $address,
        'profile_picture' => $profilePicture
    ];

    saveUserData($_SESSION['username'], $profileData);
    $updateSuccess = true;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalização de Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
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
    <div class="container">
        <h2 class="mt-5">Personalização de Perfil</h2>

        <?php if ($updateSuccess): ?>
            <div class="alert alert-success">Perfil atualizado com sucesso!</div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="mb-4">
            <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($profileData['name'] ?? '', ENT_QUOTES); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($profileData['phone'] ?? '', ENT_QUOTES); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Endereço</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($profileData['address'] ?? '', ENT_QUOTES); ?>" required>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Foto de Perfil</label>
                <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                <?php if (!empty($profileData['profile_picture']) && file_exists($profileData['profile_picture'])): ?>
                    <img src="<?php echo htmlspecialchars($profileData['profile_picture'], ENT_QUOTES); ?>" alt="Foto de Perfil" class="profile-picture mt-3">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <a href="dashboard.php" class="btn btn-secondary">Voltar ao Dashboard</a>
    </div>
</body>
</html>

<?php include('footer.php'); ?>

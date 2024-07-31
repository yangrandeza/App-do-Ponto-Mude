<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Clock In Mude'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php
    // Inclua o menu lateral apenas se não estiver nas páginas de login ou registro
    if ($_SERVER['PHP_SELF'] !== '/login.php' && $_SERVER['PHP_SELF'] !== '/register.php'): ?>
        <?php include('sidebar.php'); ?>
    <?php endif; ?>

    <div class="main-content">
        <!-- O conteúdo principal da página vai aqui -->

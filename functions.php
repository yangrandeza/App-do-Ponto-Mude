<?php
// Função para verificar se o usuário é admin
function isAdmin($username) {
    $file = "data/users.json"; // Arquivo de usuários
    if (!file_exists($file)) {
        return false;
    }
    $users = json_decode(file_get_contents($file), true);
    return isset($users[$username]) && $users[$username]['role'] === 'admin';
}

// Função para obter os dados do perfil do usuário
function getUserData($username) {
    $file = "data/$username.json";
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return $data['profile'] ?? [];
}

function getUserAttendanceData($username) {
    $file = "data/$username.json";
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    unset($data['profile']); // Remover dados do perfil do usuário
    return $data;
}
?>

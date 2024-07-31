<?php
function getUsers() {
    return json_decode(file_get_contents('data/users.json'), true);
}

function saveUsers($users) {
    file_put_contents('data/users.json', json_encode($users));
}

function getUserData($username) {
    $filename = "data/$username.json";
    return file_exists($filename) ? json_decode(file_get_contents($filename), true) : [];
}

function saveUserData($username, $data) {
    file_put_contents("data/$username.json", json_encode($data));
}
?>

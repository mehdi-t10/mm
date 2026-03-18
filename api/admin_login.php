<?php
require_once 'utils.php';

$users = readJson('users.json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

foreach ($users as $user) {
    if (
        $user['email'] === $email &&
        $user['password'] === $password &&
        $user['role'] === 'admin'
    ) {
        jsonResponse([
            'success' => true,
            'message' => 'Connexion admin réussie'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Accès refusé'
]);
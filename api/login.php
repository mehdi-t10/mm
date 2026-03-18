<?php
require_once 'utils.php';

$users = readJson('users.json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

foreach ($users as $user) {
    if ($user['email'] === $email && $user['password'] === $password) {
        jsonResponse([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Email ou mot de passe incorrect'
]);
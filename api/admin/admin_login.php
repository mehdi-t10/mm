<?php
require_once __DIR__ . '/../utils.php';

$users = readJson('users.json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

foreach ($users as $user) {
    if (
        $user['email'] === $email &&
        $user['role'] === 'admin' &&
        password_verify($password, $user['password'])
    ) {
        // Créer une copie sans le mot de passe pour la réponse
        $userResponse = $user;
        unset($userResponse['password']);
        
        jsonResponse([
            'success' => true,
            'message' => 'Connexion admin réussie',
            'user' => $userResponse
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Email ou mot de passe incorrect'
]);
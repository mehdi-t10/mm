<?php
require_once __DIR__ . '/../utils.php';

$users = readJson('users.json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$passwordUpdated = false;
foreach ($users as &$user) {
    if ($user['email'] === $email) {
        if (password_verify($password, $user['password'])) {
            if (!password_get_info($user['password'])['algo']) {
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $passwordUpdated = true;
            }
            if ($passwordUpdated) {
                writeJson('users.json', $users);
            }
            jsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user
            ]);
        }

        // Support legacy plain-text passwords and upgrade to hash
        if ($user['password'] === $password) {
            $user['password'] = password_hash($password, PASSWORD_DEFAULT);
            writeJson('users.json', $users);
            jsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user
            ]);
        }
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Email ou mot de passe incorrect'
]);
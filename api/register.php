<?php
require_once 'utils.php';

$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$telephone = $_POST['telephone'] ?? '';

// Validation
if (!$nom || !$prenom || !$email || !$password || !$telephone) {
    jsonResponse([
        'success' => false,
        'message' => 'Tous les champs sont requis'
    ]);
}

if (strlen($password) < 6) {
    jsonResponse([
        'success' => false,
        'message' => 'Le mot de passe doit avoir au moins 6 caractères'
    ]);
}

// Check if email already exists
$users = readJson('users.json');

foreach ($users as $user) {
    if ($user['email'] === $email) {
        jsonResponse([
            'success' => false,
            'message' => 'Cet email est déjà utilisé'
        ]);
    }
}

// Create new user
$newUser = [
    'id' => nextId($users),
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'password' => $password,
    'telephone' => $telephone,
    'role' => 'client'
];

$users[] = $newUser;
writeJson('users.json', $users);

jsonResponse([
    'success' => true,
    'message' => 'Compte créé avec succès',
    'user' => $newUser
]);

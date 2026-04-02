<?php
require_once 'utils.php';

$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validation
if (!$nom || !$prenom || !$email || !$password) {
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

// Create new admin user
$newAdmin = [
    'id' => nextId($users),
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'password' => $password,
    'role' => 'admin'
];

$users[] = $newAdmin;
writeJson('users.json', $users);

jsonResponse([
    'success' => true,
    'message' => 'Compte admin créé avec succès',
    'user' => $newAdmin
]);

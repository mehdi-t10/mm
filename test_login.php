<?php
require 'api/utils.php';

$email = 'demo@footcamp.test';
$password = 'demo2026';

$users = readJson('users.json');

foreach ($users as $user) {
    if ($user['email'] === $email && $user['password'] === $password) {
        echo "✓ Client trouvé!\n";
        echo json_encode($user, JSON_PRETTY_PRINT);
        exit;
    }
}

echo "✗ Client non trouvé\n";
echo "Utilisateurs chargés: " . count($users) . "\n";
var_dump($users[0]);

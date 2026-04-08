<?php
require_once __DIR__ . '/../utils.php';

$email = $_POST['email'] ?? '';
$type = $_POST['type'] ?? 'client';

if (!$email) {
    jsonResponse([
        'success' => false,
        'message' => 'Email requis'
    ]);
}

// Charger les utilisateurs
$users = readJson('users.json');

// Chercher l'utilisateur
$user = null;
foreach ($users as $u) {
    if ($u['email'] === $email && $u['type'] === $type) {
        $user = $u;
        break;
    }
}

if (!$user) {
    jsonResponse([
        'success' => false,
        'message' => 'Utilisateur non trouve'
    ]);
}

// Generer un nouveau mot de passe temporaire
$tempPassword = bin2hex(random_bytes(6)); // 12 caracteres

// Mettre a jour l'utilisateur avec le nouveau mot de passe
foreach ($users as &$u) {
    if ($u['email'] === $email && $u['type'] === $type) {
        $u['password'] = $tempPassword;
        break;
    }
}

writeJson('users.json', $users);

// Envoyer un email avec le nouveau mot de passe
$subject = "FootCamp Dreams - Reinitialisation de mot de passe";
$message = "Bonjour " . $user['prenom'] . ",\n\n";
$message .= "Votre mot de passe a ete reinitialise.\n";
$message .= "Votre nouveau mot de passe provisoire est: " . $tempPassword . "\n\n";
$message .= "Connectez-vous avec ce mot de passe et changez-le ensuite.\n\n";
$message .= "Cordialement,\nL'equipe FootCamp Dreams";

$headers = "From: noreply@footcamp-dreams.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($user['email'], $subject, $message, $headers);

jsonResponse([
    'success' => true,
    'message' => 'Un email a ete envoye avec votre nouveau mot de passe'
]);

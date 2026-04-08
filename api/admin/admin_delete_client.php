<?php
require_once __DIR__ . '/../utils.php';

header('Content-Type: application/json');

// Vérifier que c'est un admin
$currentAdmin = $_POST['currentAdmin'] ?? null;

if (!$currentAdmin) {
    jsonResponse([
        'success' => false,
        'message' => 'Non autorisé'
    ]);
}

$adminData = json_decode($currentAdmin, true);
if (!$adminData || $adminData['role'] !== 'admin') {
    jsonResponse([
        'success' => false,
        'message' => 'Accès refusé'
    ]);
}

$clientId = (int)($_POST['clientId'] ?? 0);

if ($clientId <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'ID client invalide'
    ]);
}

$users = readJson('users.json');
$reservations = readJson('reservations.json');

// Trouver et supprimer le client avec cet ID
$userFound = false;
$newUsers = [];

foreach ($users as $user) {
    if ($user['id'] !== $clientId) {
        $newUsers[] = $user;
    } else {
        $userFound = true;
    }
}

if (!$userFound) {
    jsonResponse([
        'success' => false,
        'message' => 'Client non trouvé'
    ]);
}

// Supprimer aussi ses réservations
$newReservations = [];
foreach ($reservations as $res) {
    if ($res['client_id'] !== $clientId && $res['id'] !== $clientId) {
        $newReservations[] = $res;
    }
}

writeJson('users.json', $newUsers);
writeJson('reservations.json', $newReservations);

jsonResponse([
    'success' => true,
    'message' => 'Client supprimé avec succès'
]);
?>

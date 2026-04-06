<?php
require_once 'utils.php';

header('Content-Type: application/json');

$email = isset($_GET['email']) ? trim($_GET['email']) : null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse([
        'success' => false,
        'message' => 'Email requis ou invalide'
    ]);
}

$reservations = readJson('reservations.json');
$myReservations = [];

foreach ($reservations as $res) {
    if ($res['email'] === $email) {
        $myReservations[] = $res;
    }
}

// Trier par date de création décroissante (plus récentes d'abord)
usort($myReservations, function($a, $b) {
    $dateA = strtotime($a['created_at'] ?? '2026-01-01');
    $dateB = strtotime($b['created_at'] ?? '2026-01-01');
    return $dateB - $dateA;
});

jsonResponse([
    'success' => true,
    'reservations' => $myReservations
]);
?>

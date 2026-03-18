<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');

$id = (int)($_POST['id'] ?? 0);
$deposit = (float)($_POST['deposit'] ?? 0);

foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $id) {
        $reservation['deposit'] = $deposit;
        writeJson('reservations.json', $reservations);

        jsonResponse([
            'success' => true,
            'message' => 'Arrhes mises à jour.'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Réservation introuvable.'
]);
<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');

$id = (int)($_POST['id'] ?? 0);
$discount = (int)($_POST['discount'] ?? 0);

foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $id) {
        $reservation['discount_percent'] = $discount;
        writeJson('reservations.json', $reservations);

        jsonResponse([
            'success' => true,
            'message' => 'Réduction mise à jour.'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Réservation introuvable.'
]);
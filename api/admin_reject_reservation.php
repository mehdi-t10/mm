<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');

$id = (int)($_POST['id'] ?? 0);

foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $id) {
        if ($reservation['status'] !== 'en_attente') {
            jsonResponse([
                'success' => false,
                'message' => 'Cette réservation a déjà été traitée.'
            ]);
        }

        $reservation['status'] = 'rejetee';
        writeJson('reservations.json', $reservations);

        jsonResponse([
            'success' => true,
            'message' => 'Réservation rejetée.'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Réservation introuvable.'
]);

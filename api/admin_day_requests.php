<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');

$day = $_GET['day'] ?? '';
$result = [];

foreach ($reservations as $reservation) {
    if (
        $reservation['status'] === 'validee' &&
        $day >= $reservation['date_arrivee'] &&
        $day < $reservation['date_depart']
    ) {
        $result[] = $reservation;
    }
}

jsonResponse([
    'success' => true,
    'reservations' => $result
]);
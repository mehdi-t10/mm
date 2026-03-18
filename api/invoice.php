<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');
$settings = readJson('settings.json');

$email = $_POST['email'] ?? '';
$targetReservation = null;

foreach ($reservations as $reservation) {
    if ($reservation['email'] === $email && $reservation['status'] === 'validee') {
        $targetReservation = $reservation;
        break;
    }
}

if ($targetReservation === null) {
    jsonResponse([
        'success' => false,
        'message' => 'Aucune réservation validée trouvée.'
    ]);
}

$nights = nightsCount($targetReservation['date_arrivee'], $targetReservation['date_depart']);
$roomTotal = $nights * $targetReservation['nb_personnes'] * $settings['price_per_night'];

$activitiesTotal = 0;
foreach ($settings['activities_catalog'] as $activity) {
    if (in_array($activity['name'], $targetReservation['activities'], true)) {
        $activitiesTotal += $activity['price'] * $nights * $targetReservation['nb_personnes'];
    }
}

$servicesTotal = 0;
foreach ($targetReservation['services'] as $service) {
    $servicesTotal += $service['price'] * $service['quantity'];
}

$subtotal = $roomTotal + $activitiesTotal + $servicesTotal;
$discountAmount = $subtotal * ($targetReservation['discount_percent'] / 100);
$total = $subtotal - $discountAmount - $targetReservation['deposit'];

jsonResponse([
    'success' => true,
    'room_total' => $roomTotal,
    'activities_total' => $activitiesTotal,
    'services_total' => $servicesTotal,
    'discount_amount' => $discountAmount,
    'deposit' => $targetReservation['deposit'],
    'total' => $total
]);
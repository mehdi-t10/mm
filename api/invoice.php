<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');
$settings = readJson('settings.json');
$rooms = readJson('rooms.json');

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

// Trouver le prix par nuit selon le type de chambre
$pricePerNight = $settings['price_per_night']; // Default fallback
if (isset($targetReservation['room_type'])) {
    foreach ($rooms['types'] as $roomType) {
        if ($roomType['type'] === $targetReservation['room_type']) {
            $pricePerNight = $roomType['price_per_night'];
            break;
        }
    }
}

$nights = nightsCount($targetReservation['date_arrivee'], $targetReservation['date_depart']);
$roomTotal = $nights * $pricePerNight;

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
$total = $subtotal - $discountAmount;

jsonResponse([
    'success' => true,
    'room_total' => $roomTotal,
    'activities_total' => $activitiesTotal,
    'services_total' => $servicesTotal,
    'discount_amount' => $discountAmount,
    'total' => $total
]);
<?php
require_once __DIR__ . '/../utils.php';

$reservation_id = $_GET['id'] ?? 0;

if (!$reservation_id) {
    jsonResponse([
        'success' => false,
        'message' => 'ID de reservation requis'
    ]);
}

$reservations = readJson('reservations.json');
$users = readJson('users.json');
$rooms = readJson('rooms.json');
$activities = readJson('activities.json');

// Trouver la reservation
$reservation = null;
foreach ($reservations as $r) {
    if ($r['id'] == $reservation_id) {
        $reservation = $r;
        break;
    }
}

if (!$reservation) {
    jsonResponse([
        'success' => false,
        'message' => 'Reservation non trouvee'
    ]);
}

// Trouver l'utilisateur
$user = null;
foreach ($users as $u) {
    if ($u['email'] === $reservation['email']) {
        $user = $u;
        break;
    }
}

// Trouver la chambre
$room = null;
foreach ($rooms as $r) {
    if ($r['id'] === $reservation['room']) {
        $room = $r;
        break;
    }
}

// Calculer les couts
$arrival = strtotime($reservation['date_arrivee']);
$departure = strtotime($reservation['date_depart']);
$nights = ($departure - $arrival) / (24 * 3600);

$roomPrice = ($room && isset($room['price_per_night'])) ? $room['price_per_night'] * $nights : 0;
$activitiesCost = 0;

foreach ($reservation['activities'] as $actId) {
    foreach ($activities as $act) {
        if ($act['id'] === $actId) {
            $activitiesCost += $act['price'];
            break;
        }
    }
}

$subtotal = $roomPrice + $activitiesCost;
$totalDue = $subtotal;

// Reponse JSON
jsonResponse([
    'success' => true,
    'invoice' => [
        'id' => $reservation['id'],
        'date' => $reservation['created_at'] ?? date('Y-m-d H:i:s'),
        'client_name' => ($user ? $user['prenom'] . ' ' . $user['nom'] : $reservation['prenom'] . ' ' . $reservation['nom']),
        'client_email' => $reservation['email'],
        'client_phone' => $reservation['telephone'],
        'room_name' => $room ? $room['name'] : 'N/A',
        'check_in' => $reservation['date_arrivee'],
        'check_out' => $reservation['date_depart'],
        'nights' => intval($nights),
        'room_price_per_night' => $room ? $room['price_per_night'] : 0,
        'room_total' => round($roomPrice, 2),
        'activities_cost' => round($activitiesCost, 2),
        'subtotal' => round($subtotal, 2),
        'amount_due' => round($totalDue, 2),
        'status' => $reservation['status']
    ]
]);

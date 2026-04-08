<?php
require_once __DIR__ . '/../utils.php';

$email = $_GET['email'] ?? '';

if (!$email) {
    jsonResponse([
        'success' => false,
        'message' => 'Email requis'
    ]);
}

$reservations = readJson('reservations.json');
$rooms = readJson('rooms.json');
$activities = readJson('activities.json');

$userReservations = [];

foreach ($reservations as $reservation) {
    if ($reservation['email'] === $email && $reservation['status'] === 'validee') {
        // Calculer les couts
        $arrival = strtotime($reservation['date_arrivee']);
        $departure = strtotime($reservation['date_depart']);
        $nights = ($departure - $arrival) / (24 * 3600);

        // Trouver la chambre
        $room = null;
        foreach ($rooms as $r) {
            if ($r['id'] === $reservation['room']) {
                $room = $r;
                break;
            }
        }

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
        $totalPaid = $reservation['total_paid'] ?? 0;
        $stillDue = $totalDue - $totalPaid;

        $userReservations[] = [
            'id' => $reservation['id'],
            'room_name' => $room ? $room['name'] : 'N/A',
            'check_in' => $reservation['date_arrivee'],
            'check_out' => $reservation['date_depart'],
             'nights' => intval($nights),
             'subtotal' => round($subtotal, 2),
             'total_due' => round($totalDue, 2),
            'total_paid' => round($totalPaid, 2),
            'still_due' => round(max(0, $stillDue), 2),
            'payment_status' => $stillDue <= 0 ? 'Paye' : 'En attente'
        ];
    }
}

jsonResponse([
    'success' => true,
    'reservations' => $userReservations
]);

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
$roomsData = readJson('rooms.json');
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

// Mapper les numéros de chambre aux types
$roomTypesMap = [];
$roomTypeInfo = [];
if (isset($roomsData['rooms']) && is_array($roomsData['rooms'])) {
    foreach ($roomsData['rooms'] as $r) {
        $roomTypesMap[$r['number']] = $r['type'];
    }
}
if (isset($roomsData['types']) && is_array($roomsData['types'])) {
    foreach ($roomsData['types'] as $t) {
        $roomTypeInfo[$t['type']] = $t;
    }
}

// Calculer les couts
$arrival = strtotime($reservation['date_arrivee']);
$departure = strtotime($reservation['date_depart']);
$nights = ($departure - $arrival) / (24 * 3600);

// Calculer le prix de l'hébergement basé sur room_numbers
$roomPrice = 0;
$roomNameLabel = 'N/A';

if (isset($reservation['room_numbers']) && is_array($reservation['room_numbers'])) {
    $roomNames = [];
    foreach ($reservation['room_numbers'] as $roomNum) {
        if (isset($roomTypesMap[$roomNum])) {
            $roomType = $roomTypesMap[$roomNum];
            if (isset($roomTypeInfo[$roomType])) {
                $pricePerNight = $roomTypeInfo[$roomType]['price_per_night'] ?? 0;
                $roomPrice += $pricePerNight * $nights;
                $roomNames[] = $roomTypeInfo[$roomType]['name'];
            }
        }
    }
    if (!empty($roomNames)) {
        $roomNameLabel = implode(', ', array_unique($roomNames)) . ' (' . count($reservation['room_numbers']) . ' ch.)';
    }
}

// Calculer les activités - basé sur activities_by_day pour facturer par jour
$activitiesCost = 0;
$activitiesDetails = [];
$activitiesCount = []; // Compter combien de fois chaque activité apparaît

// Compter les occurrences de chaque activité par jour
if (isset($reservation['activities_by_day']) && is_array($reservation['activities_by_day'])) {
    foreach ($reservation['activities_by_day'] as $dayActivities) {
        if (is_array($dayActivities)) {
            foreach ($dayActivities as $actId) {
                $actId = intval($actId);
                $activitiesCount[$actId] = ($activitiesCount[$actId] ?? 0) + 1;
            }
        }
    }
}

// Calculer le coût basé sur le nombre de jours
foreach ($activitiesCount as $actId => $count) {
    foreach ($activities as $act) {
        if ((int)$act['id'] === $actId) {
            $priceForActivity = ($act['price'] ?? 0) * $count;
            $activitiesCost += $priceForActivity;
            $activitiesDetails[] = [
                'id' => $act['id'],
                'name' => $act['name'] ?? 'Activité',
                'icon' => $act['icon'] ?? '',
                'price' => $act['price'] ?? 0,
                'days' => $count,
                'total' => $priceForActivity
            ];
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
        'room_name' => $roomNameLabel,
        'check_in' => $reservation['date_arrivee'],
        'check_out' => $reservation['date_depart'],
        'nights' => intval($nights),
        'room_price_per_night' => !empty($reservation['room_numbers']) ? 'Variable selon type' : 0,
        'room_total' => round($roomPrice, 2),
        'activities_cost' => round($activitiesCost, 2),
        'activities_details' => $activitiesDetails,
        'activities_by_day' => $reservation['activities_by_day'] ?? [],
        'subtotal' => round($subtotal, 2),
        'amount_due' => round($totalDue, 2),
        'status' => $reservation['status']
    ]
]);



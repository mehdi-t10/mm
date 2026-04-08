<?php
require_once __DIR__ . '/../utils.php';

/**
 * API pour récupérer l'état des chambres pour une plage de dates
 * GET params:
 * - room_type: 'simple', 'double', 'triple' (optionnel)
 * - date_start: 'YYYY-MM-DD'
 * - date_end: 'YYYY-MM-DD'
 */

$room_type = $_GET['room_type'] ?? null;
$date_start = $_GET['date_start'] ?? null;
$date_end = $_GET['date_end'] ?? null;

if (!$date_start || !$date_end) {
    jsonResponse([
        'success' => false,
        'message' => 'Dates requises (date_start, date_end)'
    ]);
}

$rooms_data = readJson('rooms.json');
$reservations = readJson('reservations.json');

// Filtrer les chambres par type si demandé
$rooms = $rooms_data['rooms'];
if ($room_type) {
    $rooms = array_filter($rooms, function($r) use ($room_type) {
        return $r['type'] === $room_type;
    });
}

// Trier les chambres par numéro
usort($rooms, function($a, $b) {
    return $a['number'] - $b['number'];
});

// Construire l'état occupé pour chaque chambre et chaque jour
$result = [];

foreach ($rooms as $room) {
    $room_info = [
        'number' => $room['number'],
        'type' => $room['type'],
        'floor' => $room['floor'],
        'days' => []
    ];

    // Parcourir chaque jour de la plage
    $current = strtotime($date_start);
    $end = strtotime($date_end);
    
    for ($date = $current; $date <= $end; $date += 86400) {
        $day = date('Y-m-d', $date);
        
        // Vérifier s'il y a une réservation validée pour cette chambre et ce jour
        $is_occupied = false;
        $reservation_id = null;
        foreach ($reservations as $res) {
            if ($res['status'] === 'validee') {
                // Vérifier les deux formats: room_number (ancien) et room_numbers array (nouveau)
                $reserved_room_numbers = [];
                
                if (isset($res['room_numbers']) && is_array($res['room_numbers'])) {
                    $reserved_room_numbers = $res['room_numbers'];
                } elseif (isset($res['room_number'])) {
                    $reserved_room_numbers = [$res['room_number']];
                }
                
                // Vérifier si cette chambre est dans la réservation
                if (in_array($room['number'], $reserved_room_numbers)) {
                    $res_start = strtotime($res['date_arrivee']);
                    $res_end = strtotime($res['date_depart']);
                    
                    // Vérifier si le jour est dans la période de réservation
                    if ($date >= $res_start && $date < $res_end) {
                        $is_occupied = true;
                        $reservation_id = $res['id'];
                        break;
                    }
                }
            }
        }
        
        $room_info['days'][$day] = [
            'date' => $day,
            'occupied' => $is_occupied,
            'reservation_id' => $reservation_id
        ];
    }
    
    $result[] = $room_info;
}

jsonResponse([
    'success' => true,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'room_type' => $room_type,
    'rooms' => $result,
    'total_rooms' => count($result),
    'date_range' => ceil((strtotime($date_end) - strtotime($date_start)) / 86400) . ' days'
]);

<?php
require_once 'utils.php';

/**
 * Attribution automatique des chambres pour une réservation
 * POST params:
 * - reservation_id: ID de la réservation
 * 
 * Attribue les chambres en ordre croissant par numéro
 */

$reservation_id = $_POST['reservation_id'] ?? null;

if (!$reservation_id) {
    jsonResponse([
        'success' => false,
        'message' => 'Reservation ID requis'
    ]);
}

$reservations = readJson('reservations.json');
$rooms_data = readJson('rooms.json');
$all_rooms = $rooms_data['rooms'];

// Trouver la réservation
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
        'message' => 'Réservation non trouvée'
    ]);
}

// Récupérer les chambres sélectionnées par le client
$selected_rooms = $reservation['selected_rooms'] ?? [];
$arrival = strtotime($reservation['date_arrivee']);
$departure = strtotime($reservation['date_depart']);

// Attribuer les chambres automatiquement
$assigned_rooms = [];

foreach ($selected_rooms as $room_type => $quantity) {
    if ($quantity <= 0) continue;
    
    // Trouver les chambres libres de ce type, en ordre croissant
    $type_rooms = array_filter($all_rooms, function($r) use ($room_type) {
        return $r['type'] === $room_type;
    });
    
    // Trier par numéro croissant
    usort($type_rooms, function($a, $b) {
        return $a['number'] - $b['number'];
    });
    
    $assigned_count = 0;
    foreach ($type_rooms as $room) {
        if ($assigned_count >= $quantity) break;
        
        // Vérifier si la chambre est disponible
        $is_available = true;
        foreach ($reservations as $res) {
            if ($res['id'] == $reservation_id) continue; // Skip la réservation actuelle
            
            if ($res['status'] === 'validee') {
                // Vérifier les deux formats: room_numbers (array) et room_number (ancien format)
                $reserved_room_numbers = isset($res['room_numbers']) ? $res['room_numbers'] : 
                                       (isset($res['room_number']) ? [$res['room_number']] : []);
                
                if (in_array($room['number'], $reserved_room_numbers)) {
                    $res_start = strtotime($res['date_arrivee']);
                    $res_end = strtotime($res['date_depart']);
                    
                    // Vérifier chevauchement
                    if (!($departure <= $res_start || $arrival >= $res_end)) {
                        $is_available = false;
                        break;
                    }
                }
            }
        }
        
        if ($is_available) {
            $assigned_rooms[] = $room['number'];
            $assigned_count++;
        }
    }
    
    // Vérifier si on a pu assigner toutes les chambres demandées
    if ($assigned_count < $quantity) {
        jsonResponse([
            'success' => false,
            'message' => "Pas assez de chambres ${room_type} disponibles. Besoin: ${quantity}, Trouvées: ${assigned_count}"
        ]);
    }
}

// Sauvegarder l'attribution
$reservation['room_numbers'] = $assigned_rooms;
$reservation['room_number'] = $assigned_rooms[0] ?? null; // Compatible ancien format
$reservation['status'] = 'validee';

// Mettre à jour la réservation
for ($i = 0; $i < count($reservations); $i++) {
    if ($reservations[$i]['id'] == $reservation_id) {
        $reservations[$i] = $reservation;
        break;
    }
}

writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Chambres attribuées avec succès',
    'assigned_rooms' => $assigned_rooms,
    'reservation_id' => $reservation_id,
    'credentials' => [
        'email' => $reservation['email'],
        'password' => 'Generated'
    ],
    'email_sent' => false
]);

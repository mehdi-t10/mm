<?php
require_once __DIR__ . '/../utils.php';

$activities = json_decode($_POST['activities'] ?? '[]', true);
$date_arrivee = $_POST['date_arrivee'] ?? '';
$date_depart = $_POST['date_depart'] ?? '';
$nb_personnes = (int)($_POST['nb_personnes'] ?? 1);

if (!$date_arrivee || !$date_depart) {
    jsonResponse([
        'success' => false,
        'message' => 'Dates requises'
    ]);
}

// Charger activites et chambres
$allActivities = readJson('activities.json');
$roomTypes = readJson('rooms.json');
$reservations = readJson('reservations.json');

// Obtenir les IDs des chambres disponibles pour les activites selectionnees
$availableFacilities = [];
if (!empty($activities)) {
    foreach ($allActivities as $activity) {
        if (in_array($activity['id'], $activities)) {
            $availableFacilities = array_merge($availableFacilities, $activity['facilities']);
        }
    }
    $availableFacilities = array_unique($availableFacilities);
} else {
    // Si aucune activite selectionnee, tous les types de chambres sont disponibles
    $availableFacilities = array_map(function($r) { return $r['id']; }, $roomTypes);
}

// Calculer le nombre de nuits
$arrival = strtotime($date_arrivee);
$departure = strtotime($date_depart);
$nights = ($departure - $arrival) / (24 * 3600);

// Obtenir toutes les chambres et les réservations
$allRoomData = readJson('rooms.json');
$roomTypes = $allRoomData['types'];
$allRooms = $allRoomData['rooms'];

// Filtrer les chambres et ajouter les informations de tarif
$result = [];
foreach ($roomTypes as $roomType) {
    if (in_array($roomType['id'], $availableFacilities)) {
        // Vérifier si la chambre a assez de capacite pour le nombre de personnes
        if ($roomType['capacity'] < $nb_personnes) {
            continue; // Sauter ce type de chambre
        }
        
        // Compter les réservations validees et autres chambres disponibles pour ce type
        $reservedCount = 0;
        $availableRoomNumbers = [];
        
        // Récupérer tous les numéros de chambres disponibles du type
        $typeRooms = array_filter($allRooms, function($r) use ($roomType) {
            return $r['type'] === $roomType['type'];
        });
        
        foreach ($typeRooms as $room) {
            $isAvailable = true;
            
            // Vérifier si cette chambre a une réservation chevauchante
            foreach ($reservations as $res) {
                if ($res['status'] === 'validee' && isset($res['room_number']) && $res['room_number'] == $room['number']) {
                    $resStart = strtotime($res['date_arrivee']);
                    $resEnd = strtotime($res['date_depart']);
                    
                    // Vérifier chevauchement
                    if (!($departure <= $resStart || $arrival >= $resEnd)) {
                        $isAvailable = false;
                        $reservedCount++;
                        break;
                    }
                }
            }
            
            if ($isAvailable) {
                $availableRoomNumbers[] = $room['number'];
            }
        }
        
        // Vérifier si au moins 1 chambre de ce type est disponible
        if (count($availableRoomNumbers) > 0) {
            $roomData = $roomType;
            $roomData['total_price'] = $roomType['price_per_night'] * $nights;
            $roomData['nights'] = $nights;
            $roomData['reserved_count'] = $reservedCount;
            $roomData['available_count'] = count($availableRoomNumbers);
            $roomData['available_numbers'] = $availableRoomNumbers;
            $result[] = $roomData;
        }
    }
}

jsonResponse([
    'success' => true,
    'rooms' => $result,
    'nights' => $nights
]);

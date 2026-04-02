<?php
require_once 'utils.php';

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
$rooms = readJson('rooms.json');
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
    // Si aucune activite selectionnee, toutes les chambres sont disponibles
    $availableFacilities = array_map(function($r) { return $r['id']; }, $rooms);
}

// Calculer le nombre de nuits
$arrival = strtotime($date_arrivee);
$departure = strtotime($date_depart);
$nights = ($departure - $arrival) / (24 * 3600);

// Filtrer les chambres et ajouter les informations de tarif
$result = [];
foreach ($rooms as $room) {
    if (in_array($room['id'], $availableFacilities)) {
        // Verifier si la chambre a assez de capacite pour le nombre de personnes
        if ($room['capacity'] < $nb_personnes) {
            continue; // Sauter cette chambre
        }
        
        // Verifier si la chambre est disponible pour les dates
        $isAvailable = true;
        foreach ($reservations as $res) {
            if ($res['room'] === $room['id'] && $res['status'] === 'validee') {
                $resStart = strtotime($res['date_arrivee']);
                $resEnd = strtotime($res['date_depart']);
                
                // Verifier chevauchement
                if (!($departure <= $resStart || $arrival >= $resEnd)) {
                    $isAvailable = false;
                    break;
                }
            }
        }
        
        if ($isAvailable) {
            $roomData = $room;
            $roomData['total_price'] = $room['price_per_night'] * $nights;
            $roomData['nights'] = $nights;
            $result[] = $roomData;
        }
    }
}

jsonResponse([
    'success' => true,
    'rooms' => $result,
    'nights' => $nights
]);

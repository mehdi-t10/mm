<?php
require_once 'utils.php';

/**
 * API pour assigner un numéro de chambre à une réservation
 * POST params:
 * - reservation_id: ID de la réservation
 * - room_number: Numéro de la chambre à assigner
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
}

$reservation_id = isset($_POST['reservation_id']) ? intval($_POST['reservation_id']) : 0;
$room_number = isset($_POST['room_number']) ? intval($_POST['room_number']) : 0;

if ($reservation_id <= 0 || $room_number <= 0) {
    jsonResponse(['success' => false, 'message' => 'Paramètres invalides']);
}

$reservations = readJson('reservations.json');
$rooms_data = readJson('rooms.json');
$all_rooms = $rooms_data['rooms'];

// Trouver la réservation
$reservation = null;
$reservationIndex = -1;
foreach ($reservations as $idx => $res) {
    if ($res['id'] === $reservation_id) {
        $reservation = $res;
        $reservationIndex = $idx;
        break;
    }
}

if (!$reservation) {
    jsonResponse(['success' => false, 'message' => 'Réservation introuvable']);
}

if ($reservation['status'] !== 'validee' && $reservation['status'] !== 'en_attente') {
    jsonResponse(['success' => false, 'message' => 'Cette réservation ne peut pas être modifiée']);
}

// Vérifier que la chambre existe et du bon type
$room = null;
foreach ($all_rooms as $r) {
    if ($r['number'] === $room_number) {
        $room = $r;
        break;
    }
}

if (!$room) {
    jsonResponse(['success' => false, 'message' => 'Chambre introuvable']);
}

// Vérifier que la chambre correspond au type de la réservation
if ($room['type'] !== $reservation['room_type']) {
    jsonResponse(['success' => false, 'message' => 'Le type de chambre ne correspond pas']);
}

// Vérifier que la chambre est disponible pour les dates de la réservation
foreach ($reservations as $res) {
    if ($res['id'] !== $reservation_id && $res['status'] === 'validee' && isset($res['room_number']) && $res['room_number'] == $room_number) {
        $res_start = strtotime($res['date_arrivee']);
        $res_end = strtotime($res['date_depart']);
        $req_start = strtotime($reservation['date_arrivee']);
        $req_end = strtotime($reservation['date_depart']);
        
        // Vérifier chevauchement
        if (!($req_end <= $res_start || $req_start >= $res_end)) {
            jsonResponse(['success' => false, 'message' => 'Cette chambre est déjà réservée pour ces dates']);
        }
    }
}

// Assigner la chambre
$reservations[$reservationIndex]['room_number'] = $room_number;
$reservations[$reservationIndex]['room'] = 'Chb ' . $room_number;

writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Chambre ' . $room_number . ' assignée avec succès',
    'reservation_id' => $reservation_id,
    'room_number' => $room_number
]);

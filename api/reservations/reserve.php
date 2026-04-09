<?php
require_once __DIR__ . '/../utils.php';

// Validate required fields
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$date_arrivee = $_POST['date_arrivee'] ?? '';
$date_depart = $_POST['date_depart'] ?? '';
$nb_personnes = (int)($_POST['nb_personnes'] ?? 1);

if (!$nom || !$prenom || !$email || !$telephone || !$date_arrivee || !$date_depart) {
    jsonResponse([
        'success' => false,
        'message' => 'Tous les champs sont requis'
    ]);
}

// Validate date constraint: arrival must be before departure
$arrival = new DateTime($date_arrivee);
$departure = new DateTime($date_depart);

if ($arrival >= $departure) {
    jsonResponse([
        'success' => false,
        'message' => 'La date d\'arrivée doit être antérieure à la date de départ'
    ]);
}

// Validate number of persons
if ($nb_personnes < 1 || $nb_personnes > 10) {
    jsonResponse([
        'success' => false,
        'message' => 'Le nombre de personnes doit être entre 1 et 10'
    ]);
}

$reservations = readJson('reservations.json');

$activities = [];
if (!empty($_POST['activities'])) {
    $activities = json_decode($_POST['activities'], true);
    if (!is_array($activities)) {
        $activities = [];
    }
}

// Handle selected_rooms (multiple rooms from client-dashboard with quantities)
$selected_rooms = [];
$room_numbers = [];
if (!empty($_POST['selected_rooms'])) {
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    if (!is_array($selected_rooms)) {
        $selected_rooms = [];
    }
}

// Handle selected_services (prestations)
$selected_services = [];
if (!empty($_POST['selected_services'])) {
    $selected_services = json_decode($_POST['selected_services'], true);
    if (!is_array($selected_services)) {
        $selected_services = [];
    }
}

$newReservation = [
    'id' => nextId($reservations),
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'date_arrivee' => $date_arrivee,
    'date_depart' => $date_depart,
    'nb_personnes' => $nb_personnes,
    'activities' => $activities,
    'activities_by_day' => [], // Will be set by admin when validating
    'selected_facilities' => [],
    'selected_rooms' => $selected_rooms, // Store selected room types and quantities
    'selected_services' => $selected_services, // Store selected services/prestations
    'services' => [],
    'discount_percent' => 0,
    'status' => 'en_attente',
    'room' => null,
    'room_type' => $_POST['room_type'] ?? null,
    'room_id' => $_POST['room_id'] ?? null,
    'room_numbers' => $room_numbers,
    'room_number' => null,
    'created_at' => date('Y-m-d H:i:s'),
    'deposit' => 80 // Default deposit
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Demande de réservation enregistrée.',
    'reservation_id' => $newReservation['id']
]);
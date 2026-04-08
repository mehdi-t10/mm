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
    'services' => [],
    'discount_percent' => 0,
    'status' => 'en_attente',
    'room' => '',
    'room_type' => $_POST['room_type'] ?? '',
    'room_id' => $_POST['room_id'] ?? ''
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Demande de réservation enregistrée.',
    'reservation_id' => $newReservation['id']
]);
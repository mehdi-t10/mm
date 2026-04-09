<?php
require_once __DIR__ . '/../utils.php';

// Lire les données JSON du corps de la requête
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$nom = $input['nom'] ?? '';
$prenom = $input['prenom'] ?? '';
$email = $input['email'] ?? '';
$telephone = $input['telephone'] ?? '';
$date_arrivee = $input['date_arrivee'] ?? '';
$date_depart = $input['date_depart'] ?? '';
$nb_personnes = $input['nb_personnes'] ?? 0;
$activities = $input['activities'] ?? [];
$activities_by_day = $input['activities_by_day'] ?? [];
$selected_facilities = $input['selected_facilities'] ?? [];
$selected_rooms = $input['selected_rooms'] ?? [];
$selected_services = $input['selected_services'] ?? [];

// Validation
if (!$nom || !$prenom || !$email || !$telephone || !$date_arrivee || !$date_depart) {
    jsonResponse([
        'success' => false,
        'message' => 'Tous les champs obligatoires sont requis'
    ]);
}

// Validate phone format
if (!preg_match('/^[0-9]{10}$/', $telephone)) {
    jsonResponse([
        'success' => false,
        'message' => 'Téléphone invalide (10 chiffres requis)'
    ]);
}

// Validate dates
$arrival = strtotime($date_arrivee);
$departure = strtotime($date_depart);

if (!$arrival || !$departure || $arrival >= $departure) {
    jsonResponse([
        'success' => false,
        'message' => 'Les dates sont invalides (arrivée < départ)'
    ]);
}

// Create reservation
$reservations = readJson('reservations.json');

$newReservation = [
    'id' => nextId($reservations),
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'date_arrivee' => $date_arrivee,
    'date_depart' => $date_depart,
    'nb_personnes' => intval($nb_personnes),
    'activities' => is_array($activities) ? $activities : [],
    'activities_by_day' => is_array($activities_by_day) ? $activities_by_day : [],
    'selected_facilities' => is_array($selected_facilities) ? $selected_facilities : [],
    'selected_rooms' => is_array($selected_rooms) ? $selected_rooms : [],
    'selected_services' => is_array($selected_services) ? $selected_services : [],
    'status' => 'en_attente',
    'deposit' => 80,
    'room' => null,
    'room_type' => $input['room_type'] ?? null,
    'room_id' => $input['room_id'] ?? null,
    'created_at' => date('Y-m-d H:i:s')
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

// DÉSACTIVÉ: Envoi d'email ne fonctionne pas
// $emailSubject = ...
// $emailBody = ...
// $mailSent = sendEmailViaSMTP(...)
// logEmail(...)

jsonResponse([
    'success' => true,
    'message' => 'Reservation soumise avec succes! (Email de confirmation désactivé)',
    'reservation_id' => $newReservation['id'],
    'email_sent' => false
]);

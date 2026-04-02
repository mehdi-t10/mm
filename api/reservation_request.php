<?php
require_once 'utils.php';

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
    'status' => 'en_attente',
    'deposit' => 80,
    'room' => null,
    'created_at' => date('Y-m-d H:i:s')
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Réservation soumise avec succès! Un administrateur l\'examinera et vous enverra un email avec vos identifiants.',
    'reservation_id' => $newReservation['id']
]);

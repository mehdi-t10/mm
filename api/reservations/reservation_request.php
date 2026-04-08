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
    'status' => 'en_attente',
    'deposit' => 80,
    'room' => null,
    'room_type' => $input['room_type'] ?? null,
    'room_id' => $input['room_id'] ?? null,
    'created_at' => date('Y-m-d H:i:s')
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

// Envoyer un email de confirmation
$emailSubject = 'FootCamp Dreams - Reservation recue!';
$emailBody = "Bonjour $prenom $nom,

Nous avons bien recu votre demande de reservation pour votre sejour au centre FootCamp Dreams.

DETAILS DE VOTRE DEMANDE:
- Arrivee: " . date('d/m/Y', $arrival) . "
- Depart: " . date('d/m/Y', $departure) . "
- Nombre de personnes: $nb_personnes
- Depot: 80€

Un administrateur examinera votre demande et vous enverra un email avec:
- Votre numero de reservation
- Vos identifiants de connexion
- Les details complets de votre facture
- Les instructions de paiement

Ce processus peut prendre 24h. Merci d'avoir choisi FootCamp Dreams!

Cordialement,
L'equipe FootCamp Dreams";

$mailSent = sendEmailViaSMTP($email, $emailSubject, $emailBody);

logEmail([
    'timestamp' => date('Y-m-d H:i:s'),
    'to' => $email,
    'type' => 'reservation_confirmation',
    'reservation_id' => $newReservation['id'],
    'subject' => $emailSubject,
    'body' => $emailBody,
    'sent' => $mailSent
]);

jsonResponse([
    'success' => true,
    'message' => 'Reservation soumise avec succes! Un email de confirmation a ete envoye a ' . $email . '. Un administrateur examinera votre demande et vous enverra un email avec vos identifiants.',
    'reservation_id' => $newReservation['id'],
    'email_sent' => $mailSent
]);

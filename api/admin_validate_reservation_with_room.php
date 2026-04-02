<?php
require_once 'utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
}

$reservationId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$roomId = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

if ($reservationId <= 0 || $roomId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Paramètres invalides']);
}

$reservations = readJson('reservations.json');
$rooms = readJson('rooms.json');
$users = readJson('users.json');

// Trouver la réservation
$reservation = null;
$reservationIndex = -1;
foreach ($reservations as $idx => $res) {
    if ($res['id'] === $reservationId) {
        $reservation = $res;
        $reservationIndex = $idx;
        break;
    }
}

if (!$reservation) {
    jsonResponse(['success' => false, 'message' => 'Réservation introuvable']);
}

if ($reservation['status'] !== 'en_attente') {
    jsonResponse(['success' => false, 'message' => 'Cette réservation a déjà été traitée']);
}

// Trouver et valider la chambre
$room = null;
$roomIndex = -1;
foreach ($rooms as $idx => $r) {
    if ($r['id'] === $roomId) {
        $room = $r;
        $roomIndex = $idx;
        break;
    }
}

if (!$room) {
    jsonResponse(['success' => false, 'message' => 'Chambre introuvable']);
}

// Vérifier la disponibilité
if ($room['occupied'] >= $room['capacity']) {
    jsonResponse(['success' => false, 'message' => 'Chambre pleine']);
}

// Mettre à jour les données
$rooms[$roomIndex]['occupied'] += 1;
$reservation['status'] = 'validee';
$reservation['room'] = $room['id'];
$reservations[$reservationIndex] = $reservation;

// Créer/mettre à jour compte utilisateur
$alreadyExists = false;
$existingUser = null;
foreach ($users as $user) {
    if ($user['email'] === $reservation['email']) {
        $alreadyExists = true;
        $existingUser = $user;
        break;
    }
}

if (!$alreadyExists) {
    // Créer nouveau compte
    $password = generatePassword($reservation['prenom']);
    $users[] = [
        'id' => nextId($users),
        'nom' => $reservation['nom'],
        'prenom' => $reservation['prenom'],
        'email' => $reservation['email'],
        'password' => $password,
        'telephone' => $reservation['telephone'],
        'type' => 'client'
    ];
} else {
    $password = $existingUser['password'];
}

// Sauvegarder tous les changements
writeJson('reservations.json', $reservations);
writeJson('rooms.json', $rooms);
writeJson('users.json', $users);

// Préparer l'email avec les identifiants
$emailSubject = 'FootCamp Dreams - Votre réservation validée! Accès client';
$emailBody = "Bonjour {$reservation['prenom']} {$reservation['nom']},

Félicitations! Votre réservation a été validée avec succès!

========== INFORMATIONS DE CONNEXION ==========
Email: {$reservation['email']}
Mot de passe: $password

========== DÉTAILS DE VOTRE RÉSERVATION ==========
Numéro de réservation: #{$reservationId}
Chambre: {$room['name']}
Arrivée: " . date('d/m/Y', strtotime($reservation['date_arrivee'])) . "
Départ: " . date('d/m/Y', strtotime($reservation['date_depart'])) . "
Nombre de personnes: {$reservation['nb_personnes']}
Acompte versé: {$reservation['deposit']}€

========== ACCÈS CLIENT ==========
Connectez-vous à votre compte: https://footcamp-dreams.local/client-dashboard.html

Une fois connecté, vous pourrez:
- Consulter les détails complets de votre réservation
- Voir votre facture détaillée
- Gérer votre séjour

Bienvenue chez FootCamp Dreams!
Merci d'avoir choisi notre établissement.

Cordialement,
L'équipe FootCamp Dreams";

$headers = "From: noreply@footcamp-dreams.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Tenter l'envoi (may fail si SMTP not configured)
$mailSent = @mail($reservation['email'], $emailSubject, $emailBody, $headers);

// Enregistrer dans logs
logEmail([
    'timestamp' => date('Y-m-d H:i:s'),
    'to' => $reservation['email'],
    'type' => 'credentials',
    'reservation_id' => $reservationId,
    'subject' => $emailSubject,
    'body' => $emailBody,
    'sent' => $mailSent
]);

jsonResponse([
    'success' => true,
    'message' => 'Réservation validée avec succès',
    'room_name' => $room['name'],
    'room_id' => $room['id'],
    'credentials' => [
        'email' => $reservation['email'],
        'password' => $password
    ],
    'email_sent' => $mailSent,
    'reservation_id' => $reservationId
]);

function logEmail($data) {
    $logsFile = 'data/email_logs.json';
    $logs = [];
    
    if (file_exists($logsFile)) {
        $logs = json_decode(file_get_contents($logsFile), true) ?? [];
    }
    
    $logs[] = $data;
    file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>

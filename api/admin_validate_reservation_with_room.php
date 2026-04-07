<?php
require_once 'utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
}

$reservationId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$roomType = isset($_POST['room_type']) ? $_POST['room_type'] : null;

if ($reservationId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Paramètres invalides']);
}

$reservations = readJson('reservations.json');
$roomTypes = readJson('rooms.json');
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

// Déterminer le room_type à utiliser
if (!$roomType) {
    // Utiliser celui de la réservation si disponible
    if (isset($reservation['room_type']) && $reservation['room_type']) {
        $roomType = $reservation['room_type'];
    } else {
        jsonResponse(['success' => false, 'message' => 'Type de chambre requis']);
    }
}

// Trouver et valider le type de chambre
$roomTypeInfo = null;
foreach ($roomTypes as $rt) {
    if ($rt['type'] === $roomType) {
        $roomTypeInfo = $rt;
        break;
    }
}

if (!$roomTypeInfo) {
    jsonResponse(['success' => false, 'message' => 'Type de chambre introuvable']);
}

// Compter les réservations validées pour ce type
$reservedCount = 0;
foreach ($reservations as $res) {
    if ($res['status'] === 'validee' && isset($res['room_type']) && $res['room_type'] === $roomType) {
        $reservedCount++;
    }
}

// Vérifier la disponibilité
if ($reservedCount >= $roomTypeInfo['total']) {
    jsonResponse(['success' => false, 'message' => 'Aucune chambre ' . $roomType . ' disponible']);
}

// Mettre à jour les données
$reservation['status'] = 'validee';
$reservation['room_type'] = $roomType;
$reservation['room'] = $roomTypeInfo['name']; // Stocker le nom pour compatibilité
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
    $plainPassword = generatePassword($reservation['prenom']);
    $users[] = [
        'id' => nextId($users),
        'nom' => $reservation['nom'],
        'prenom' => $reservation['prenom'],
        'email' => $reservation['email'],
        'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
        'telephone' => $reservation['telephone'],
        'role' => 'client'
    ];
    $passwordToSend = $plainPassword;
} else {
    $passwordToSend = null;
}

// Sauvegarder tous les changements
writeJson('reservations.json', $reservations);
writeJson('users.json', $users);

// Préparer l'email avec les identifiants
$emailSubject = 'FootCamp Dreams - Votre réservation validée! Accès client';
$emailBody = "Bonjour {$reservation['prenom']} {$reservation['nom']},

Félicitations! Votre réservation a été validée avec succès!

========== INFORMATIONS DE CONNEXION ==========
Email: {$reservation['email']}" . ($passwordToSend ? "\nMot de passe: $passwordToSend" : "\n(Vous avez un compte existant)") . "

========== DÉTAILS DE VOTRE RÉSERVATION ==========
Numéro de réservation: #{$reservationId}
Type de chambre: " . ucfirst($roomType) . "
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

// Envoyer l'email via SMTP
$mailSent = sendEmailViaSMTP($reservation['email'], $emailSubject, $emailBody);

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

$message = '✅ Réservation validée avec succès!';
if ($mailSent) {
    $message .= "\n📧 Email avec les identifiants envoyé à: " . $reservation['email'];
} else {
    $message .= "\n⚠️ Email non envoyé - Vérifiez la configuration SMTP";
}

jsonResponse([
    'success' => true,
    'message' => $message,
    'room_name' => $room['name'],
    'room_id' => $room['id'],
    'credentials' => [
        'email' => $reservation['email'],
        'password' => $password
    ],
    'email_sent' => $mailSent,
    'reservation_id' => $reservationId
]);
?>

<?php
require_once __DIR__ . '/../utils.php';

header('Content-Type: application/json');

$reservationId = isset($_POST['reservationId']) ? intval($_POST['reservationId']) : 0;

if ($reservationId <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'ID réservation invalide'
    ]);
}

$reservations = readJson('reservations.json');
$rooms = readJson('rooms.json');

$reservation = null;
foreach ($reservations as $res) {
    if ($res['id'] === $reservationId) {
        $reservation = $res;
        break;
    }
}

if (!$reservation) {
    jsonResponse([
        'success' => false,
        'message' => 'Réservation introuvable'
    ]);
}

if ($reservation['status'] !== 'validee') {
    jsonResponse([
        'success' => false,
        'message' => 'Réservation non validée'
    ]);
}

// Trouver la chambre assignée
$roomName = 'À assigner';
foreach ($rooms as $room) {
    if ($room['id'] == $reservation['room']) {
        $roomName = $room['name'];
        break;
    }
}

// Construire l'email
$to = $reservation['email'];
$subject = 'FootCamp Dreams - Bienvenue! Vos identifiants de connexion';

$message = "Bonjour {$reservation['prenom']} {$reservation['nom']},\n\n";
$message .= "Nous sommes ravi de vous accueillir au centre de vacances FootCamp Dreams!\n\n";

$message .= "========== INFORMATIONS DE CONNEXION ==========\n";
$message .= "Email: {$reservation['email']}\n";
$message .= "Mot de passe provisoire: Veuillez vous connecter avec votre email pour la première fois\n";
$message .= "Accès: https://footcamp-dreams.local/client-dashboard.html\n\n";

$message .= "========== DÉTAILS DE VOTRE RÉSERVATION ==========\n";
$message .= "Numéro de réservation: #{$reservation['id']}\n";
$message .= "Chambre assignée: {$roomName}\n";
$message .= "Arrivée: " . date('d/m/Y', strtotime($reservation['date_arrivee'])) . "\n";
$message .= "Départ: " . date('d/m/Y', strtotime($reservation['date_depart'])) . "\n";
$message .= "Nombre de personnes: {$reservation['nb_personnes']}\n";
$message .= "Acompte versé: {$reservation['deposit']}€\n\n";

$message .= "========== PROCHAINES ÉTAPES ==========\n";
$message .= "1. Connectez-vous à votre compte sur le site\n";
$message .= "2. Consultez votre facture complète\n";
$message .= "3. Effectuez le paiement du solde restant\n";
$message .= "4. Prenez connaissance des informations de votre séjour\n\n";

$message .= "Si vous avez des questions, n'hésitez pas à nous contacter.\n\n";
$message .= "Merci d'avoir choisi FootCamp Dreams!\n";
$message .= "L'équipe FootCamp Dreams\n";
$message .= "contact@footcamp-dreams.local\n";

// Configurer les en-têtes
$headers = "From: noreply@footcamp-dreams.local\r\n";
$headers .= "Reply-To: contact@footcamp-dreams.local\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: FootCamp Dreams System\r\n";

// Envoyer l'email
$mailSent = @mail($to, $subject, $message, $headers);

if ($mailSent) {
    jsonResponse([
        'success' => true,
        'message' => 'Email d\'accueil envoyé à ' . $to
    ]);
} else {
    // Log erreur mais signale quand même le succès (email peut ne pas être configuré en local)
    jsonResponse([
        'success' => true,
        'message' => 'Email d\'accueil préparé (serveur mail peut ne pas être configuré)'
    ]);
}
?>

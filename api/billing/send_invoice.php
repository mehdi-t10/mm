<?php
require_once __DIR__ . '/../utils.php';

$reservation_id = $_POST['reservation_id'] ?? 0;

if (!$reservation_id) {
    jsonResponse([
        'success' => false,
        'message' => 'ID de reservation requis'
    ]);
}

$reservations = readJson('reservations.json');
$users = readJson('users.json');
$rooms = readJson('rooms.json');
$activities = readJson('activities.json');

// Trouver la reservation
$reservation = null;
foreach ($reservations as $r) {
    if ($r['id'] == $reservation_id) {
        $reservation = $r;
        break;
    }
}

if (!$reservation) {
    jsonResponse([
        'success' => false,
        'message' => 'Reservation non trouvee'
    ]);
}

// Trouver l'utilisateur
$user = null;
foreach ($users as $u) {
    if ($u['email'] === $reservation['email']) {
        $user = $u;
        break;
    }
}

// Trouver la chambre
$room = null;
foreach ($rooms as $r) {
    if ($r['id'] === $reservation['room']) {
        $room = $r;
        break;
    }
}

// Calculer les couts
$arrival = strtotime($reservation['date_arrivee']);
$departure = strtotime($reservation['date_depart']);
$nights = ($departure - $arrival) / (24 * 3600);

$roomPrice = ($room && isset($room['price_per_night'])) ? $room['price_per_night'] * $nights : 0;
$activitiesCost = 0;

foreach ($reservation['activities'] as $actId) {
    foreach ($activities as $act) {
        if ($act['id'] === $actId) {
            $activitiesCost += $act['price'];
            break;
        }
    }
}

$subtotal = $roomPrice + $activitiesCost;
$deposit = $reservation['deposit'] ?? 0;
$totalDue = $subtotal - $deposit;

$clientName = ($user ? $user['prenom'] . ' ' . $user['nom'] : $reservation['prenom'] . ' ' . $reservation['nom']);
$clientEmail = $reservation['email'];

// Construire le email avec la facture
$subject = "FootCamp Dreams - Votre Facture (Reservation #" . $reservation['id'] . ")";

$message = "Bonjour " . $clientName . ",\n\n";
$message .= "Voici les details de votre facture:\n\n";
$message .= "========================================\n";
$message .= "FACTURE - Reservation #" . $reservation['id'] . "\n";
$message .= "========================================\n\n";

$message .= "CLIENT:\n";
$message .= $clientName . "\n";
$message .= $clientEmail . "\n";
$message .= $reservation['telephone'] . "\n\n";

$message .= "SEJOUR:\n";
$message .= "Chambre: " . ($room ? $room['name'] : 'N/A') . "\n";
$message .= "Arrivee: " . $reservation['date_arrivee'] . "\n";
$message .= "Depart: " . $reservation['date_depart'] . "\n";
$message .= "Nombre de nuits: " . intval($nights) . "\n\n";

$message .= "COUTS:\n";
$message .= "Chambre (" . intval($nights) . " nuits x " . ($room ? $room['price_per_night'] : 0) . "€): " . round($roomPrice, 2) . "€\n";
if ($activitiesCost > 0) {
    $message .= "Activites: " . round($activitiesCost, 2) . "€\n";
}
$message .= "Sous-total: " . round($subtotal, 2) . "€\n";
$message .= "Acompte (deposit): -" . round($deposit, 2) . "€\n";
$message .= "--------\n";
$message .= "MONTANT DU: " . round($totalDue, 2) . "€\n\n";

$message .= "Le solde devra etre verse avant votre arrivee.\n\n";
$message .= "Merci d'avoir choisi FootCamp Dreams!\n";
$message .= "Nous vous souhaitons un agreable sejour.\n\n";
$message .= "Cordialement,\n";
$message .= "L'equipe FootCamp Dreams\n";

$headers = "From: noreply@footcamp-dreams.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$mailSent = mail($clientEmail, $subject, $message, $headers);

jsonResponse([
    'success' => $mailSent,
    'message' => $mailSent ? 'Facture envoyee par email' : 'Erreur lors de l\'envoi de la facture'
]);

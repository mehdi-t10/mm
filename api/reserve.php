<?php
require_once 'utils.php';

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
    'nom' => $_POST['nom'] ?? '',
    'prenom' => $_POST['prenom'] ?? '',
    'email' => $_POST['email'] ?? '',
    'telephone' => $_POST['telephone'] ?? '',
    'date_arrivee' => $_POST['date_arrivee'] ?? '',
    'date_depart' => $_POST['date_depart'] ?? '',
    'nb_personnes' => (int)($_POST['nb_personnes'] ?? 1),
    'activities' => $activities,
    'services' => [],
    'discount_percent' => 0,
    'deposit' => 80,
    'status' => 'en_attente',
    'room' => ''
];

$reservations[] = $newReservation;
writeJson('reservations.json', $reservations);

jsonResponse([
    'success' => true,
    'message' => 'Demande de réservation enregistrée.'
]);
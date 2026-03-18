<?php
require_once 'utils.php';

$plannedActivities = readJson('planned_activities.json');
$reservations = readJson('reservations.json');

$reservationId = (int)($_POST['reservation_id'] ?? 0);
$day = $_POST['day'] ?? '';
$activity = $_POST['activity'] ?? '';
$coach = $_POST['coach'] ?? '';
$referee = $_POST['referee'] ?? '';
$participants = (int)($_POST['participants'] ?? 0);

$found = false;
foreach ($reservations as $reservation) {
    if ($reservation['id'] === $reservationId && $reservation['status'] === 'validee') {
        $found = true;
        break;
    }
}

if (!$found) {
    jsonResponse([
        'success' => false,
        'message' => 'Réservation validée introuvable.'
    ]);
}

$plannedActivities[] = [
    'id' => nextId($plannedActivities),
    'reservation_id' => $reservationId,
    'day' => $day,
    'activity' => $activity,
    'coach' => $coach,
    'referee' => $referee,
    'participants' => $participants
];

writeJson('planned_activities.json', $plannedActivities);

jsonResponse([
    'success' => true,
    'message' => 'Activité planifiée avec succès.'
]);

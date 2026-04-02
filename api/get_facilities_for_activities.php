<?php
require_once 'utils.php';

header('Content-Type: application/json');

// Récupérer les IDs des activités sélectionnées
$activityIds = isset($_GET['activities']) ? explode(',', $_GET['activities']) : [];
$activityIds = array_map('intval', array_filter($activityIds));

if (empty($activityIds)) {
    jsonResponse([
        'success' => true,
        'facilities' => []
    ]);
}

$activities = readJson('activities.json');
$facilities = readJson('facilities.json');

// Collecter les IDs des facilities utilisées par les activités sélectionnées
$facilityIds = [];
foreach ($activities as $activity) {
    if (in_array($activity['id'], $activityIds)) {
        $facilityIds = array_merge($facilityIds, $activity['facilities'] ?? []);
    }
}

$facilityIds = array_unique($facilityIds);

// Retourner les facilities correspondantes
$result = [];
foreach ($facilities as $facility) {
    if (in_array($facility['id'], $facilityIds)) {
        $result[] = $facility;
    }
}

jsonResponse([
    'success' => true,
    'facilities' => $result
]);
?>

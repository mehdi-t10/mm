<?php
require_once __DIR__ . '/../utils.php';

$activities = $_GET['activities'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if (!$activities || !$start_date || !$end_date) {
    jsonResponse(['success' => false, 'message' => 'Paramètres manquants']);
}

$activityIds = explode(',', $activities);
$facilities = readJson('facilities.json');
$activitiesData = readJson('activities.json');

$availableFacilities = [];

foreach ($activityIds as $activityId) {
    $activity = null;
    foreach ($activitiesData as $act) {
        if ($act['id'] == $activityId) {
            $activity = $act;
            break;
        }
    }
    if ($activity) {
        foreach ($activity['facilities'] as $facilityId) {
            $facility = null;
            foreach ($facilities as $fac) {
                if ($fac['id'] == $facilityId) {
                    $facility = $fac;
                    break;
                }
            }
            if ($facility) {
                $availableFacilities[] = [
                    'activity_id' => $activityId,
                    'activity_name' => $activity['name'],
                    'facility' => $facility
                ];
            }
        }
    }
}

jsonResponse(['success' => true, 'facilities' => $availableFacilities]);
?>
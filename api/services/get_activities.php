<?php
require_once __DIR__ . '/../utils.php';

header('Content-Type: application/json; charset=utf-8');

$activities = readJson('activities.json');

if (!is_array($activities)) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur lors de la lecture des activités'
    ]);
} else {
    jsonResponse([
        'success' => true,
        'activities' => $activities
    ]);
}


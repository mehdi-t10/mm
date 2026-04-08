<?php
require_once __DIR__ . '/../utils.php';

$reservations = readJson('reservations.json');

jsonResponse([
    'success' => true,
    'reservations' => $reservations
]);
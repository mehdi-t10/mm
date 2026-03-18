<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');

jsonResponse([
    'success' => true,
    'reservations' => $reservations
]);
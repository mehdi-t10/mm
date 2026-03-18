<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');
$settings = readJson('settings.json');

$email = $_POST['email'] ?? '';
$serviceName = $_POST['service_name'] ?? '';
$quantity = (int)($_POST['quantity'] ?? 1);

$servicePrice = 0;
foreach ($settings['services_catalog'] as $service) {
    if ($service['name'] === $serviceName) {
        $servicePrice = $service['price'];
        break;
    }
}

foreach ($reservations as &$reservation) {
    if ($reservation['email'] === $email && $reservation['status'] === 'validee') {
        $reservation['services'][] = [
            'name' => $serviceName,
            'quantity' => $quantity,
            'price' => $servicePrice
        ];

        writeJson('reservations.json', $reservations);

        jsonResponse([
            'success' => true,
            'message' => 'Prestation ajoutée.'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Aucune réservation validée trouvée pour cet email.'
]);
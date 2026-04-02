<?php
require_once 'utils.php';

$reservation_id = (int)($_POST['reservation_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? 'carte';

if (!$reservation_id || $amount <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'Donnees de paiement invalides'
    ]);
}

$reservations = readJson('reservations.json');

// Trouver la reservation
$reservationFound = false;
foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $reservation_id) {
        $reservationFound = true;
        
        // Verifier que c'est une reservation validee
        if ($reservation['status'] !== 'validee') {
            jsonResponse([
                'success' => false,
                'message' => 'Cette reservation ne peut pas recevoir de paiement'
            ]);
        }

        // Ajouter ou creer le champ payments s'il n'existe pas
        if (!isset($reservation['payments'])) {
            $reservation['payments'] = [];
        }

        // Enregistrer le paiement
        $payment = [
            'date' => date('Y-m-d H:i:s'),
            'amount' => $amount,
            'method' => $payment_method,
            'status' => 'completed'
        ];
        
        $reservation['payments'][] = $payment;

        // Calculer le total paye
        $totalPaid = 0;
        foreach ($reservation['payments'] as $p) {
            if ($p['status'] === 'completed') {
                $totalPaid += $p['amount'];
            }
        }

        $reservation['total_paid'] = $totalPaid;

        writeJson('reservations.json', $reservations);

        jsonResponse([
            'success' => true,
            'message' => 'Paiement enregistre avec succes',
            'total_paid' => $totalPaid,
            'remaining' => 'Voir la facture pour le montant restant'
        ]);
    }
}

if (!$reservationFound) {
    jsonResponse([
        'success' => false,
        'message' => 'Reservation non trouvee'
    ]);
}

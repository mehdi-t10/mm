<?php
require_once 'utils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
}

$reservationId = isset($_POST['reservationId']) ? intval($_POST['reservationId']) : 0;

if (!$reservationId) {
    jsonResponse(['success' => false, 'message' => 'ID réservation invalide']);
}

$reservations = readJson('reservations.json');
$rooms = readJson('rooms.json');

$reservation = null;
foreach ($reservations as $res) {
    if ($res['id'] === $reservationId) {
        $reservation = $res;
        break;
    }
}

if (!$reservation) {
    jsonResponse(['success' => false, 'message' => 'Réservation introuvable']);
}

// Calculer le montant de la facture
$arrival = new DateTime($reservation['date_arrivee']);
$departure = new DateTime($reservation['date_depart']);
$nights = $departure->diff($arrival)->days;

// Trouver le prix de la chambre
$roomPrice = 120; // prix par défaut
foreach ($rooms as $room) {
    if ($room['id'] == $reservation['room']) {
        $roomPrice = $room['price_per_night'];
        break;
    }
}

$subtotal = $roomPrice * $nights;
$discount = ($reservation['discount_percent'] ?? 0) > 0 ? ($subtotal * $reservation['discount_percent'] / 100) : 0;
$total = $subtotal - $discount;
$balance = max(0, $total - $reservation['deposit']);

// Créer HTML pour la facture
$invoiceHtml = "
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { border-bottom: 3px solid #667eea; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #667eea; font-size: 28px; }
        .header p { margin: 5px 0 0 0; color: #666; }
        .section { margin-bottom: 30px; }
        .section h2 { color: #333; font-size: 16px; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field { margin-bottom: 12px; }
        .label { font-weight: 600; color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        .value { color: #333; font-size: 14px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f5f5f5; padding: 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .summary { background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #eee; }
        .summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .summary-row.total { font-size: 18px; font-weight: bold; color: #667eea; border: none; margin-bottom: 0; }
        .summary-row.total span:last-child { font-size: 20px; }
        .deposit { background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50; }
        .balance { background: #fff3e0; padding: 10px; border-left: 4px solid #ff9800; }
        .footer { text-align: center; padding: 20px; border-top: 1px solid #ddd; color: #999; font-size: 12px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>FACTURE</h1>
            <p>FootCamp Dreams - Centre de vacances</p>
        </div>

        <div class='section'>
            <h2>Informations Client</h2>
            <div class='grid'>
                <div>
                    <div class='field'>
                        <div class='label'>Nom</div>
                        <div class='value'>" . htmlspecialchars($reservation['nom']) . " " . htmlspecialchars($reservation['prenom']) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Email</div>
                        <div class='value'>" . htmlspecialchars($reservation['email']) . "</div>
                    </div>
                </div>
                <div>
                    <div class='field'>
                        <div class='label'>Numéro de Réservation</div>
                        <div class='value'>#" . str_pad($reservationId, 6, '0', STR_PAD_LEFT) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Date d'Émission</div>
                        <div class='value'>" . date('d/m/Y') . "</div>
                    </div>
                </div>
            </div>
        </div>

        <div class='section'>
            <h2>Détails du Séjour</h2>
            <div class='grid'>
                <div>
                    <div class='field'>
                        <div class='label'>Arrivée</div>
                        <div class='value'>" . date('d/m/Y', strtotime($reservation['date_arrivee'])) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Nombre de Nuits</div>
                        <div class='value'>$nights</div>
                    </div>
                </div>
                <div>
                    <div class='field'>
                        <div class='label'>Départ</div>
                        <div class='value'>" . date('d/m/Y', strtotime($reservation['date_depart'])) . "</div>
                    </div>
                    <div class='field'>
                        <div class='label'>Nombre de Personnes</div>
                        <div class='value'>" . (int)$reservation['nb_personnes'] . "</div>
                    </div>
                </div>
            </div>
        </div>

        <div class='section'>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class='text-right'>Quantité</th>
                        <th class='text-right'>Prix Unitaire</th>
                        <th class='text-right'>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hébergement en chambre</td>
                        <td class='text-right'>$nights nuit(s)</td>
                        <td class='text-right'>" . number_format($roomPrice, 2, ',', ' ') . "€</td>
                        <td class='text-right'><strong>" . number_format($subtotal, 2, ',', ' ') . "€</strong></td>
                    </tr>";

if ($discount > 0) {
    $invoiceHtml .= "
                    <tr style='background: #f0f0f0;'>
                        <td colspan='3' style='text-align: right; font-weight: 600;'>Réduction (" . ($reservation['discount_percent'] ?? 0) . "%)</td>
                        <td class='text-right' style='color: #4caf50; font-weight: 600;'>-" . number_format($discount, 2, ',', ' ') . "€</td>
                    </tr>";
}

$invoiceHtml .= "
                </tbody>
            </table>
        </div>

        <div class='section'>
            <div class='summary'>
                <div class='summary-row'>
                    <span>Sous-total</span>
                    <span>" . number_format($subtotal, 2, ',', ' ') . "€</span>
                </div>";

if ($discount > 0) {
    $invoiceHtml .= "
                <div class='summary-row'>
                    <span>Réduction</span>
                    <span>-" . number_format($discount, 2, ',', ' ') . "€</span>
                </div>";
}

$invoiceHtml .= "
                <div class='summary-row total'>
                    <span>Total à payer</span>
                    <span>" . number_format($total, 2, ',', ' ') . "€</span>
                </div>
                <div class='deposit' style='margin-top: 15px;'>
                    <strong>✓ Acompte versé:</strong> " . number_format($reservation['deposit'], 2, ',', ' ') . "€
                </div>";

if ($balance > 0) {
    $invoiceHtml .= "
                <div class='balance' style='margin-top: 10px;'>
                    <strong>⚠️ Solde à payer:</strong> " . number_format($balance, 2, ',', ' ') . "€
                </div>";
} else {
    $invoiceHtml .= "
                <div class='deposit' style='margin-top: 10px; background: #c8e6c9; border-left-color: #4caf50;'>
                    <strong>✓ Paiement complet reçu</strong>
                </div>";
}

$invoiceHtml .= "
            </div>
        </div>

        <div class='footer'>
            <p>Cette facture a été générée automatiquement. Merci d'avoir choisi FootCamp Dreams!</p>
            <p>Pour toute question, contactez: contact@footcamp-dreams.local</p>
        </div>
    </div>
</body>
</html>
";

// Envoyer l'email
$to = $reservation['email'];
$subject = 'Facture de réservation FootCamp Dreams #' . str_pad($reservationId, 6, '0', STR_PAD_LEFT);

$mailSent = sendEmailViaSMTP($to, $subject, $invoiceHtml, 'text/html');

// Enregistrer l'envoi dans logs
logEmailSent([
    'timestamp' => date('Y-m-d H:i:s'),
    'to' => $to,
    'type' => 'invoice',
    'reservation_id' => $reservationId,
    'subject' => $subject,
    'sent' => $mailSent
]);

jsonResponse([
    'success' => true,
    'message' => 'Facture envoyée à ' . $to,
    'html' => $invoiceHtml
]);

function logEmailSent($data) {
    $logsFile = 'data/email_logs.json';
    $logs = [];
    
    if (file_exists($logsFile)) {
        $logs = json_decode(file_get_contents($logsFile), true) ?? [];
    }
    
    $logs[] = $data;
    file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>

<?php
require_once __DIR__ . '/../utils.php';

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
$settings = readJson('settings.json');
$activities = readJson('activities.json');

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
if (isset($rooms['types']) && is_array($rooms['types'])) {
    $roomTypesMap = [];
    foreach ($rooms['rooms'] ?? [] as $r) {
        $roomTypesMap[$r['number']] = $r['type'];
    }
    
    if (!empty($reservation['room_numbers']) && is_array($reservation['room_numbers'])) {
        $roomPrice = 0;
        foreach ($reservation['room_numbers'] as $roomNum) {
            if (isset($roomTypesMap[$roomNum])) {
                $roomType = $roomTypesMap[$roomNum];
                foreach ($rooms['types'] as $type) {
                    if ($type['type'] === $roomType) {
                        $roomPrice += $type['price_per_night'] * $nights;
                        break;
                    }
                }
            }
        }
    }
}

// Calculer les activités
$activitiesCost = 0;
if (isset($reservation['activities_by_day']) && is_array($reservation['activities_by_day'])) {
    $activitiesCount = [];
    foreach ($reservation['activities_by_day'] as $dayActivities) {
        if (is_array($dayActivities)) {
            foreach ($dayActivities as $actId) {
                $actId = intval($actId);
                $activitiesCount[$actId] = ($activitiesCount[$actId] ?? 0) + 1;
            }
        }
    }
    
    foreach ($activitiesCount as $actId => $count) {
        foreach ($activities as $act) {
            if ((int)$act['id'] === $actId) {
                $activitiesCost += ($act['price'] ?? 0) * $count;
                break;
            }
        }
    }
}

// Calculer les prestations
$servicesCost = 0;
$servicesDetails = [];
if (!empty($reservation['selected_services']) && is_array($reservation['selected_services'])) {
    foreach ($reservation['selected_services'] as $serviceId => $quantity) {
        if ($quantity > 0) {
            foreach ($settings['services_catalog'] as $service) {
                if ($service['id'] === $serviceId) {
                    $servicePrice = $service['price'];

                    if ($service['type'] === 'meals') {
                        $servicePrice = $service['price'] * $reservation['nb_personnes'] * $nights;
                    } elseif ($service['type'] === 'transport_per_person' || $service['type'] === 'merchandise_per_person') {
                        $servicePrice = $service['price'] * $reservation['nb_personnes'];
                    }

                    $servicesCost += $servicePrice;
                    $servicesDetails[] = [
                        'name' => $service['name'],
                        'price' => $service['price'],
                        'total' => $servicePrice
                    ];
                    break;
                }
            }
        }
    }
}

$subtotal = $roomPrice + $activitiesCost + $servicesCost;
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
                        <td class='text-right'>" . number_format($roomPrice / $nights, 2, ',', ' ') . "€</td>
                        <td class='text-right'><strong>" . number_format($roomPrice, 2, ',', ' ') . "€</strong></td>
                    </tr>";

if ($activitiesCost > 0) {
    $invoiceHtml .= "
                    <tr>
                        <td>Activités</td>
                        <td class='text-right'>-</td>
                        <td class='text-right'>-</td>
                        <td class='text-right'><strong>" . number_format($activitiesCost, 2, ',', ' ') . "€</strong></td>
                    </tr>";
}

foreach ($servicesDetails as $service) {
    $invoiceHtml .= "
                    <tr>
                        <td>" . htmlspecialchars($service['name']) . "</td>
                        <td class='text-right'>-</td>
                        <td class='text-right'>" . number_format($service['price'], 2, ',', ' ') . "€</td>
                        <td class='text-right'><strong>" . number_format($service['total'], 2, ',', ' ') . "€</strong></td>
                    </tr>";
}

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
                    <span>Hébergement</span>
                    <span>" . number_format($roomPrice, 2, ',', ' ') . "€</span>
                </div>";

if ($activitiesCost > 0) {
    $invoiceHtml .= "
                <div class='summary-row'>
                    <span>Activités</span>
                    <span>" . number_format($activitiesCost, 2, ',', ' ') . "€</span>
                </div>";
}

if ($servicesCost > 0) {
    $invoiceHtml .= "
                <div class='summary-row'>
                    <span>Prestations</span>
                    <span>" . number_format($servicesCost, 2, ',', ' ') . "€</span>
                </div>";
}

$invoiceHtml .= "
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

if ($balance > 0) {
    $invoiceHtml .= "
                <div class='balance' style='margin-top: 10px;'>
                    <strong>⚠️ Solde à payer:</strong> " . number_format($balance, 2, ',', ' ') . "€
                </div>";
} else {
    $invoiceHtml .= "
                <div style='margin-top: 10px; background: #c8e6c9; padding: 10px; border-left: 4px solid #4caf50; border-radius: 4px;'>
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

// DÉSACTIVÉ: Envoi d'email ne fonctionne pas
// L'email reste disponible au frontend pour affichage

jsonResponse([
    'success' => true,
    'message' => 'Facture générée avec succès (envoi email désactivé)',
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

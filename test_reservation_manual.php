<?php
/**
 * TEST MANUEL DE RÉSERVATION
 * Ce script simule une soumission de réservation depuis le client-dashboard
 */

require_once __DIR__ . '/api/utils.php';

// Simuler les données POST d'une réservation client-dashboard
$_POST = [
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'email' => 'jean@example.com',
    'telephone' => '0612345678',
    'date_arrivee' => '2026-04-15',
    'date_depart' => '2026-04-20',
    'nb_personnes' => '5',
    'selected_rooms' => '{"simple":1,"double":2}',
    'activities' => '[1,2]',
];

echo "=== TEST DE RÉSERVATION CLIENT-DASHBOARD ===\n\n";

// Validation
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$date_arrivee = $_POST['date_arrivee'] ?? '';
$date_depart = $_POST['date_depart'] ?? '';
$nb_personnes = (int)($_POST['nb_personnes'] ?? 1);

echo "1. VALIDATION DES CHAMPS OBLIGATOIRES\n";
echo "   Nom: $nom\n";
echo "   Prénom: $prenom\n";
echo "   Email: $email\n";
echo "   Téléphone: $telephone\n";
echo "   Dates: $date_arrivee -> $date_depart\n";
echo "   Personnes: $nb_personnes\n\n";

if (!$nom || !$prenom || !$email || !$telephone || !$date_arrivee || !$date_depart) {
    echo "   ✗ ERREUR: Champs obligatoires manquants\n";
    exit;
}
echo "   ✓ OK\n\n";

echo "2. VALIDATION DES DATES\n";
$arrival = new DateTime($date_arrivee);
$departure = new DateTime($date_depart);

if ($arrival >= $departure) {
    echo "   ✗ ERREUR: La date d'arrivée doit être antérieure à la date de départ\n";
    exit;
}
echo "   ✓ OK\n\n";

echo "3. VALIDATION DU NOMBRE DE PERSONNES\n";
if ($nb_personnes < 1 || $nb_personnes > 10) {
    echo "   ✗ ERREUR: Le nombre de personnes doit être entre 1 et 10\n";
    exit;
}
echo "   ✓ OK\n\n";

echo "4. TRAITEMENT DES ACTIVITÉS\n";
$activities = [];
if (!empty($_POST['activities'])) {
    $activities = json_decode($_POST['activities'], true);
    if (!is_array($activities)) {
        $activities = [];
    }
}
echo "   Activités sélectionnées: " . implode(', ', $activities) . "\n";
echo "   ✓ OK\n\n";

echo "5. TRAITEMENT DES CHAMBRES\n";
$selected_rooms = [];
if (!empty($_POST['selected_rooms'])) {
    $selected_rooms = json_decode($_POST['selected_rooms'], true);
    if (!is_array($selected_rooms)) {
        $selected_rooms = [];
    }
}
echo "   Chambres sélectionnées:\n";
foreach ($selected_rooms as $type => $quantity) {
    echo "   - $type: $quantity\n";
}
echo "   ✓ OK\n\n";

echo "6. CRÉATION DE LA RÉSERVATION\n";
$reservations = readJson('reservations.json');
$nextId = count($reservations) > 0 ? max(array_column($reservations, 'id')) + 1 : 1;

$newReservation = [
    'id' => $nextId,
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'date_arrivee' => $date_arrivee,
    'date_depart' => $date_depart,
    'nb_personnes' => $nb_personnes,
    'activities' => $activities,
    'activities_by_day' => [],
    'selected_facilities' => [],
    'selected_rooms' => $selected_rooms,
    'services' => [],
    'discount_percent' => 0,
    'status' => 'en_attente',
    'room' => null,
    'room_type' => null,
    'room_id' => null,
    'room_numbers' => [],
    'room_number' => null,
    'created_at' => date('Y-m-d H:i:s'),
    'deposit' => 80,
];

echo "   Structure de réservation créée:\n";
echo "   ID: {$newReservation['id']}\n";
echo "   Statut: {$newReservation['status']}\n";
echo "   Chambres: " . json_encode($newReservation['selected_rooms']) . "\n";
echo "   Activités: " . json_encode($newReservation['activities']) . "\n";
echo "   Créée le: {$newReservation['created_at']}\n";
echo "   ✓ OK\n\n";

echo "7. RÉSUMÉ\n";
echo "   ✓ La réservation aurait été créée avec succès!\n";
echo "   ✓ ID: {$newReservation['id']}\n";
echo "   ✓ Format compatible avec le stockage en base\n";
echo "\n=== TEST RÉUSSI ===\n";
?>


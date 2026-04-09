<?php
require_once __DIR__ . '/../utils.php';

$reservations = readJson('reservations.json');
$roomTypes = readJson('rooms.json');
$users = readJson('users.json');

$id = (int)($_POST['id'] ?? 0);
$roomType = $_POST['room_type'] ?? null;

foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $id) {
        if ($reservation['status'] !== 'en_attente') {
            jsonResponse([
                'success' => false,
                'message' => 'Cette reservation a deja ete traitee.'
            ]);
        }

        // Si la réservation a déjà un room_type, l'utiliser. Sinon, utiliser le paramètre
        if (isset($reservation['room_type']) && $reservation['room_type']) {
            $roomType = $reservation['room_type'];
        } elseif (!$roomType) {
            // Assigner un type simple par défaut si aucun n'est spécifié
            $roomType = 'simple';
        }

        // Vérifier qu'au moins 1 chambre du type est disponible
        $roomTypeInfo = null;
        foreach ($roomTypes as $rt) {
            if ($rt['type'] === $roomType) {
                $roomTypeInfo = $rt;
                break;
            }
        }

        if (!$roomTypeInfo) {
            jsonResponse([
                'success' => false,
                'message' => 'Type de chambre invalide.'
            ]);
        }

        // Compter les réservations validées pour ce type
        $reservedCount = 0;
        foreach ($reservations as $res) {
            if ($res['status'] === 'validee' && isset($res['room_type']) && $res['room_type'] === $roomType) {
                $reservedCount++;
            }
        }

        // Vérifier la disponibilité
        if ($reservedCount >= $roomTypeInfo['total']) {
            jsonResponse([
                'success' => false,
                'message' => 'Aucune chambre ' . $roomType . ' disponible.'
            ]);
        }

        // Valider et assigner
        $reservation['status'] = 'validee';
        $reservation['room_type'] = $roomType;
        $reservation['room'] = $roomTypeInfo['name']; // Stocker le nom pour compatibilité

        $alreadyExists = false;
        $existingUser = null;
        foreach ($users as $user) {
            if ($user['email'] === $reservation['email']) {
                $alreadyExists = true;
                $existingUser = $user;
                break;
            }
        }

        if (!$alreadyExists) {
            // Creation d'un nouveau compte client
            $plainPassword = generatePassword($reservation['prenom']);
            $users[] = [
                'id' => nextId($users),
                'nom' => $reservation['nom'],
                'prenom' => $reservation['prenom'],
                'email' => $reservation['email'],
                'password' => password_hash($plainPassword, PASSWORD_DEFAULT),
                'telephone' => $reservation['telephone'],
                'role' => 'client'
            ];
            $passwordToSend = $plainPassword;
        } else {
            // Utiliser le password existant du client - ne pas envoyer
            $passwordToSend = null;
        }

        writeJson('reservations.json', $reservations);
        writeJson('users.json', $users);

        // Send email with credentials
        $emailSubject = 'FootCamp Dreams - Votre reservation a ete confirmee!';
        $emailBody = "Bonjour {$reservation['prenom']} {$reservation['nom']},

Votre reservation a ete validee! Vous pouvez maintenant vous connecter a votre compte.

INFORMATIONS DE CONNEXION:
- Email: {$reservation['email']}" . ($passwordToSend ? "\n- Mot de passe: $passwordToSend" : "\n(Vous avez deja un compte, utilisez votre mot de passe existant)") . "

DETAILS DE VOTRE RESERVATION:
- Type de chambre: " . ucfirst($roomType) . "
- Arrivee: {$reservation['date_arrivee']}
- Depart: {$reservation['date_depart']}
- Nombre de personnes: {$reservation['nb_personnes']}

ACCES CLIENT:
Connectez-vous a votre compte: https://footcamp-dreams.local/client-dashboard.html

Une fois connecte, vous pourrez:
- Consulter les details complets de votre reservation
- Voir votre facture detaillee
- Gerer votre sejour

Veuillez conserver ces identifiants en un lieu sur.

Cordialement,
L'equipe FootCamp Dreams";

        // DÉSACTIVÉ: Envoi d'email ne fonctionne pas
        // $mailSent = sendEmailViaSMTP($reservation['email'], $emailSubject, $emailBody);
        // logEmail(...);

        $message = '✅ Réservation validée avec succès!';
        // $message .= "\n📧 Email avec les identifiants envoyé à: " . $reservation['email'];

        jsonResponse([
            'success' => true,
            'message' => $message
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Reservation introuvable.'
]);
?>

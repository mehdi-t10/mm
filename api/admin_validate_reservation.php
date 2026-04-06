<?php
require_once 'utils.php';

$reservations = readJson('reservations.json');
$rooms = readJson('rooms.json');
$users = readJson('users.json');

$id = (int)($_POST['id'] ?? 0);

foreach ($reservations as &$reservation) {
    if ($reservation['id'] === $id) {
        if ($reservation['status'] !== 'en_attente') {
            jsonResponse([
                'success' => false,
                'message' => 'Cette reservation a deja ete traitee.'
            ]);
        }

        foreach ($rooms as &$room) {
            if ($room['occupied'] < $room['capacity']) {
                $room['occupied'] += 1;
                $reservation['status'] = 'validee';
                $reservation['room'] = $room['id'];

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
                writeJson('rooms.json', $rooms);
                writeJson('users.json', $users);

                // Send email with credentials
                $emailSubject = 'FootCamp Dreams - Votre reservation a ete confirmee!';
                $emailBody = "Bonjour {$reservation['prenom']} {$reservation['nom']},

Votre reservation a ete validee! Vous pouvez maintenant vous connecter a votre compte.

INFORMATIONS DE CONNEXION:
- Email: {$reservation['email']}" . ($passwordToSend ? "\n- Mot de passe: $passwordToSend" : "\n(Vous avez deja un compte, utilisez votre mot de passe existant)") . "

DETAILS DE VOTRE RESERVATION:
- Chambre assignee: {$room['name']}
- Arrivee: {$reservation['date_arrivee']}
- Depart: {$reservation['date_depart']}
- Nombre de personnes: {$reservation['nb_personnes']}
- Depot: {$reservation['deposit']}€

ACCES CLIENT:
Connectez-vous a votre compte: https://footcamp-dreams.local/client-dashboard.html

Une fois connecte, vous pourrez:
- Consulter les details complets de votre reservation
- Voir votre facture detaillee
- Gerer votre sejour

Veuillez conserver ces identifiants en un lieu sur.

Cordialement,
L'equipe FootCamp Dreams";

                // Envoyer l'email via SMTP
                $mailSent = sendEmailViaSMTP($reservation['email'], $emailSubject, $emailBody);

                // Enregistrer dans logs
                logEmail([
                    'timestamp' => date('Y-m-d H:i:s'),
                    'to' => $reservation['email'],
                    'type' => 'credentials',
                    'reservation_id' => $reservation['id'],
                    'subject' => $emailSubject,
                    'body' => $emailBody,
                    'sent' => $mailSent
                ]);

                $message = '✅ Réservation validée avec succès!';
                if ($mailSent) {
                    $message .= "\n📧 Email avec les identifiants envoyé à: " . $reservation['email'];
                } else {
                    $message .= "\n⚠️ Email non envoyé - Vérifiez la configuration SMTP";
                }

                jsonResponse([
                    'success' => true,
                    'message' => $message,
                    'room_name' => $room['name'],
                    'room_id' => $room['id'],
                    'credentials' => [
                        'email' => $reservation['email'],
                        'password' => $passwordToSend
                    ],
                    'email_sent' => $mailSent,
                    'reservation_id' => $reservation['id']
                ]);
            }
        }

        jsonResponse([
            'success' => false,
            'message' => 'Aucune chambre disponible.'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Reservation introuvable.'
]);
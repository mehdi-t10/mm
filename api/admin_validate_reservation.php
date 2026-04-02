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
                    $password = generatePassword($reservation['prenom']);
                    $users[] = [
                        'id' => nextId($users),
                        'nom' => $reservation['nom'],
                        'prenom' => $reservation['prenom'],
                        'email' => $reservation['email'],
                        'password' => $password,
                        'telephone' => $reservation['telephone'],
                        'type' => 'client'
                    ];
                } else {
                    // Utiliser le password existant du client
                    $password = $existingUser['password'];
                }

                writeJson('reservations.json', $reservations);
                writeJson('rooms.json', $rooms);
                writeJson('users.json', $users);

                // Send email with credentials
                $emailSubject = 'FootCamp Dreams - Votre reservation a ete confirmee!';
                $emailBody = "Bonjour {$reservation['prenom']} {$reservation['nom']},

Votre reservation a ete validee! Vous pouvez maintenant vous connecter a votre compte.

INFORMATIONS DE CONNEXION:
- Email: {$reservation['email']}
- Mot de passe: $password

DETAILS DE VOTRE RESERVATION:
- Chambre assignee: {$room['name']}
- Arrivee: {$reservation['date_arrivee']}
- Depart: {$reservation['date_depart']}
- Nombre de personnes: {$reservation['nb_personnes']}
- Depot: {$reservation['deposit']}€

Veuillez conserver ces identifiants en un lieu sur.

Cordialement,
L'equipe FootCamp Dreams";

                $headers = "From: noreply@footcamp-dreams.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                @mail($reservation['email'], $emailSubject, $emailBody, $headers);

                jsonResponse([
                    'success' => true,
                    'message' => 'Reservation validee. Email d\'identifiants envoye a ' . $reservation['email'] . '. Chambre assignee : ' . $room['name']
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
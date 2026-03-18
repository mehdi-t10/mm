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
                'message' => 'Cette réservation a déjà été traitée.'
            ]);
        }

        foreach ($rooms as &$room) {
            if ($room['occupied'] < $room['capacity']) {
                $room['occupied'] += 1;
                $reservation['status'] = 'validee';
                $reservation['room'] = $room['name'];

                $password = generatePassword($reservation['prenom']);

                $alreadyExists = false;
                foreach ($users as $user) {
                    if ($user['email'] === $reservation['email']) {
                        $alreadyExists = true;
                        break;
                    }
                }

                if (!$alreadyExists) {
                    $users[] = [
                        'id' => nextId($users),
                        'nom' => $reservation['nom'],
                        'prenom' => $reservation['prenom'],
                        'email' => $reservation['email'],
                        'password' => $password,
                        'role' => 'client'
                    ];
                }

                writeJson('reservations.json', $reservations);
                writeJson('rooms.json', $rooms);
                writeJson('users.json', $users);

                jsonResponse([
                    'success' => true,
                    'message' => 'Réservation validée. Chambre attribuée : ' . $room['name'] . '. Mot de passe client : ' . $password
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
    'message' => 'Réservation introuvable.'
]);
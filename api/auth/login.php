<?php
require_once __DIR__ . '/../utils.php';

$users = readJson('users.json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Log pour debug
file_put_contents(__DIR__ . '/../data/login_debug.log', 
    date('Y-m-d H:i:s') . " - Email: $email, Password: " . substr($password, 0, 3) . "..., Users count: " . count($users) . "\n",
    FILE_APPEND
);

$passwordUpdated = false;
foreach ($users as &$user) {
    if ($user['email'] === $email) {
        file_put_contents(__DIR__ . '/../data/login_debug.log', 
            date('Y-m-d H:i:s') . " - User trouvé: " . $user['email'] . "\n",
            FILE_APPEND
        );
        
        if (password_verify($password, $user['password'])) {
            file_put_contents(__DIR__ . '/../data/login_debug.log', 
                date('Y-m-d H:i:s') . " - Password verify réussie\n",
                FILE_APPEND
            );
            
            if (!password_get_info($user['password'])['algo']) {
                $user['password'] = password_hash($password, PASSWORD_DEFAULT);
                $passwordUpdated = true;
            }
            if ($passwordUpdated) {
                writeJson('users.json', $users);
            }
            
            // Créer une copie sans le mot de passe pour la réponse
            $userResponse = $user;
            unset($userResponse['password']);
            
            jsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $userResponse
            ]);
        }

        // Support legacy plain-text passwords and upgrade to hash
        if ($user['password'] === $password) {
            file_put_contents(__DIR__ . '/../data/login_debug.log', 
                date('Y-m-d H:i:s') . " - Password clair réussie\n",
                FILE_APPEND
            );
            
            $user['password'] = password_hash($password, PASSWORD_DEFAULT);
            writeJson('users.json', $users);
            
            // Créer une copie sans le mot de passe pour la réponse
            $userResponse = $user;
            unset($userResponse['password']);
            
            jsonResponse([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $userResponse
            ]);
        }
        
        file_put_contents(__DIR__ . '/../data/login_debug.log', 
            date('Y-m-d H:i:s') . " - Email trouvé mais password incorrecte\n",
            FILE_APPEND
        );
    }
}

file_put_contents(__DIR__ . '/../data/login_debug.log', 
    date('Y-m-d H:i:s') . " - Connexion échouée\n",
    FILE_APPEND
);

jsonResponse([
    'success' => false,
    'message' => 'Email ou mot de passe incorrect'
]);
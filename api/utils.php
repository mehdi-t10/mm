<?php

function readJson($fileName) {
    $path = __DIR__ . '/../data/' . $fileName;

    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}

function writeJson($fileName, $data) {
    $path = __DIR__ . '/../data/' . $fileName;
    file_put_contents(
        $path,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

function jsonResponse($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function nextId($items) {
    if (empty($items)) {
        return 1;
    }

    $ids = array_column($items, 'id');
    return max($ids) + 1;
}

function nightsCount($dateStart, $dateEnd) {
    $d1 = new DateTime($dateStart);
    $d2 = new DateTime($dateEnd);
    $diff = $d1->diff($d2)->days;

    return max(1, $diff);
}

function generatePassword($firstName) {
    return strtolower($firstName) . '2026';
}

function logEmail($data) {
    $logsFile = __DIR__ . '/../data/email_logs.json';
    $logs = [];
    
    if (file_exists($logsFile)) {
        $logs = json_decode(file_get_contents($logsFile), true) ?? [];
    }
    
    $logs[] = $data;
    file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function sendEmailViaSMTP($to, $subject, $body) {
    require_once __DIR__ . '/../config.php';
    
    // Vérifier la configuration
    if (SMTP_NOT_CONFIGURED) {
        return false;
    }
    
    try {
        // Créer une connexion SMTP TLS
        $socket = @stream_socket_client(
            "tcp://" . SMTP_HOST . ":" . SMTP_PORT,
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT
        );
        
        if (!$socket) {
            return false;
        }
        
        // Lire le message de bienvenue
        stream_get_line($socket, 512);
        
        // Envoyer EHLO
        fputs($socket, "EHLO localhost\r\n");
        stream_get_line($socket, 512);
        
        // Activer TLS si configuré
        if (SMTP_SECURE === 'tls') {
            fputs($socket, "STARTTLS\r\n");
            stream_get_line($socket, 512);
            
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
            } else {
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }
            
            fputs($socket, "EHLO localhost\r\n");
            stream_get_line($socket, 512);
        }
        
        // Authentification
        fputs($socket, "AUTH LOGIN\r\n");
        stream_get_line($socket, 512);
        fputs($socket, base64_encode(SMTP_USER) . "\r\n");
        stream_get_line($socket, 512);
        fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
        $authResponse = stream_get_line($socket, 512);
        
        if (strpos($authResponse, '235') === false) {
            fclose($socket);
            return false; // Authentification échouée
        }
        
        // Préparer l'email
        fputs($socket, "MAIL FROM:<" . SMTP_FROM_EMAIL . ">\r\n");
        stream_get_line($socket, 512);
        
        fputs($socket, "RCPT TO:<{$to}>\r\n");
        stream_get_line($socket, 512);
        
        fputs($socket, "DATA\r\n");
        stream_get_line($socket, 512);
        
        // Construire les headers
        $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: FootCamp Dreams\r\n";
        $headers .= "\r\n";
        
        $message = $headers . $body;
        
        // Envoyer le message
        fputs($socket, $message . "\r\n.\r\n");
        $response = stream_get_line($socket, 512);
        
        // Terminer la connexion
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return strpos($response, '250') !== false;
        
    } catch (Exception $e) {
        return false;
    }
}
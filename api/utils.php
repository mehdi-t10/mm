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

function sendEmailViaSMTP($to, $subject, $body, $contentType = 'text/plain') {
    require_once __DIR__ . '/../config.php';

    if (SMTP_NOT_CONFIGURED) {
        return false;
    }

    $socket = @stream_socket_client(
        'tcp://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 30);

    $readResponse = function () use ($socket) {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (preg_match('/^[0-9]{3} /', $line)) {
                break;
            }
        }
        return trim($response);
    };

    $writeCommand = function ($command) use ($socket) {
        fwrite($socket, $command . "\r\n");
    };

    $response = $readResponse();
    if (strpos($response, '220') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand('EHLO localhost');
    $response = $readResponse();
    if (strpos($response, '250') !== 0) {
        fclose($socket);
        return false;
    }

    if (SMTP_SECURE === 'tls') {
        $writeCommand('STARTTLS');
        $response = $readResponse();
        if (strpos($response, '220') !== 0) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }

        $writeCommand('EHLO localhost');
        $response = $readResponse();
        if (strpos($response, '250') !== 0) {
            fclose($socket);
            return false;
        }
    }

    $writeCommand('AUTH LOGIN');
    $response = $readResponse();
    if (strpos($response, '334') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand(base64_encode(SMTP_USER));
    $response = $readResponse();
    if (strpos($response, '334') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand(base64_encode(SMTP_PASSWORD));
    $response = $readResponse();
    if (strpos($response, '235') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand('MAIL FROM:<' . SMTP_FROM_EMAIL . '>');
    $response = $readResponse();
    if (strpos($response, '250') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand('RCPT TO:<' . $to . '>');
    $response = $readResponse();
    if (strpos($response, '250') !== 0 && strpos($response, '251') !== 0) {
        fclose($socket);
        return false;
    }

    $writeCommand('DATA');
    $response = $readResponse();
    if (strpos($response, '354') !== 0) {
        fclose($socket);
        return false;
    }

    $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: " . $contentType . "; charset=UTF-8\r\n";
    $headers .= "X-Mailer: FootCamp Dreams\r\n";
    $headers .= "\r\n";

    $message = $headers . $body . "\r\n.";
    $writeCommand($message);

    $response = $readResponse();
    $writeCommand('QUIT');
    fclose($socket);

    return strpos($response, '250') === 0;
}
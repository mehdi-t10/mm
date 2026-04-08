<?php
require_once __DIR__ . '/../utils.php';

header('Content-Type: application/json');

// Vérifier si c'est un appel POST pour envoyer un test
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? 'Email de Test';
    $message = $_POST['message'] ?? 'Ceci est un email de test.';
    
    if (!$email) {
        jsonResponse(['success' => false, 'message' => 'Email requis']);
    }
    
    $headers = "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: test@footcamp-dreams.com\r\n";
    
    $mailSent = @mail($email, $subject, $message, $headers);
    
    // Enregistrer dans les logs
    $logsFile = 'data/email_logs.json';
    $logs = [];
    
    if (file_exists($logsFile)) {
        $logs = json_decode(file_get_contents($logsFile), true) ?? [];
    }
    
    $logs[] = [
        'timestamp' => date('Y-m-d H:i:s'),
        'to' => $email,
        'type' => 'test',
        'subject' => $subject,
        'sent' => $mailSent,
        'message' => substr($message, 0, 100)
    ];
    
    file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    jsonResponse([
        'success' => true,
        'message' => 'Email test envoyé à ' . $email,
        'sent' => $mailSent
    ]);
}

// GET: Retourner tous les logs d'email
$logsFile = 'data/email_logs.json';
$logs = [];

if (file_exists($logsFile)) {
    $logs = json_decode(file_get_contents($logsFile), true) ?? [];
}

// Trier par date décroissante
usort($logs, function($a, $b) {
    return strtotime($b['timestamp']) - strtotime($a['timestamp']);
});

jsonResponse([
    'success' => true,
    'emails' => $logs,
    'total' => count($logs)
]);
?>

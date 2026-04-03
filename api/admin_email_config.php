<?php
require_once 'utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Charger la configuration actuelle
    $config = [];
    $configFile = __DIR__ . '/../config.php';
    
    // Lire la configuration (manière simple)
    if (file_exists($configFile)) {
        ob_start();
        include $configFile;
        ob_end_clean();
        
        $config = [
            'SMTP_USER' => defined('SMTP_USER') ? (strpos(SMTP_USER, 'your-email') !== false ? '' : SMTP_USER) : '',
            'SMTP_HOST' => SMTP_HOST,
            'SMTP_PORT' => SMTP_PORT,
            'SMTP_SECURE' => SMTP_SECURE,
            'SMTP_FROM_EMAIL' => SMTP_FROM_EMAIL,
            'SMTP_FROM_NAME' => SMTP_FROM_NAME,
            'is_configured' => !SMTP_NOT_CONFIGURED
        ];
    }
    
    jsonResponse([
        'success' => true,
        'config' => $config
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtpUser = $_POST['smtp_user'] ?? '';
    $smtpPassword = $_POST['smtp_password'] ?? '';
    $smtpFromEmail = $_POST['smtp_from_email'] ?? '';
    $smtpFromName = $_POST['smtp_from_name'] ?? '';
    
    if (empty($smtpUser) || empty($smtpPassword)) {
        jsonResponse([
            'success' => false,
            'message' => 'Email et mot de passe SMTP requis'
        ]);
    }
    
    // Générer le contenu du fichier config.php
    $configContent = "<?php
/**
 * Configuration SMTP pour l'envoi d'emails
 * Auto-généré par l'interface admin
 */

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USER', '" . addslashes($smtpUser) . "');
define('SMTP_PASSWORD', '" . addslashes($smtpPassword) . "');
define('SMTP_FROM_EMAIL', '" . addslashes($smtpFromEmail) . "');
define('SMTP_FROM_NAME', '" . addslashes($smtpFromName) . "');
define('SMTP_NOT_CONFIGURED', false);
?>";

    $configFile = __DIR__ . '/../config.php';
    
    if (file_put_contents($configFile, $configContent) !== false) {
        jsonResponse([
            'success' => true,
            'message' => 'Configuration SMTP sauvegardée avec succès'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde de la configuration'
        ]);
    }
}

jsonResponse([
    'success' => false,
    'message' => 'Méthode non autorisée'
]);
?>

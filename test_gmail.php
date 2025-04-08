<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Utiliser PHPMailer directement pour le test
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "=== Test d'envoi d'email via Gmail ===\n\n";

// Afficher les paramètres SMTP (sans le mot de passe)
echo "Configuration SMTP :\n";
echo "- Hôte : " . (defined('SMTP_HOST') ? SMTP_HOST : 'Non défini') . "\n";
echo "- Port : " . (defined('SMTP_PORT') ? SMTP_PORT : 'Non défini') . "\n";
echo "- Utilisateur : " . (defined('SMTP_USERNAME') ? SMTP_USERNAME : 'Non défini') . "\n";
echo "- Chiffrement : " . (defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls (par défaut)') . "\n";
echo "- Email d'envoi : " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'Non défini') . "\n\n";

// Email de destination pour le test
$to = "votre-email@example.com"; // Remplacer par votre adresse email pour le test
echo "Email de destination pour le test : $to\n\n";

try {
    // Créer une nouvelle instance de PHPMailer avec débogage
    $mail = new PHPMailer(true);
    
    // Activer le débogage (commentez cette ligne si vous ne voulez pas voir les détails)
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Affiche des informations détaillées
    
    // Configuration du serveur SMTP
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    
    // Configuration du chiffrement
    if (defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
    }
    
    $mail->Port = SMTP_PORT;
    
    // Options supplémentaires pour Gmail
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];
    
    // Expéditeur et destinataire
    $mail->setFrom(SMTP_FROM_EMAIL, APP_NAME);
    $mail->addAddress($to);
    
    // Contenu de l'email
    $mail->isHTML(true);
    $mail->Subject = 'Test de connexion Gmail - ' . date('Y-m-d H:i:s');
    $mail->Body = '
        <h1>Test de connexion Gmail</h1>
        <p>Ceci est un test pour vérifier que votre configuration Gmail fonctionne correctement.</p>
        <p>Si vous voyez cet email, la configuration est correcte !</p>
        <p>Date et heure du test : ' . date('Y-m-d H:i:s') . '</p>
    ';
    $mail->AltBody = 'Test de connexion Gmail - ' . date('Y-m-d H:i:s');
    
    // Envoyer l'email
    $mail->send();
    
    echo "\nL'email de test a été envoyé avec succès à $to.\n";
    
} catch (Exception $e) {
    echo "\nERREUR: L'email n'a pas pu être envoyé. \n";
    echo "Message d'erreur : " . $mail->ErrorInfo . "\n";
}

echo "\n=== Instructions pour utiliser Gmail ===\n";
echo "1. Vérifiez que vous avez bien configuré les constantes SMTP dans app/Config/Constants.php\n";
echo "2. Pour les comptes Gmail avec authentification à deux facteurs :\n";
echo "   - Vous devez créer un 'mot de passe d'application' spécifique\n";
echo "   - Allez dans votre compte Google → Sécurité → Mots de passe des applications\n";
echo "   - Générez un nouveau mot de passe pour 'Autre (nom personnalisé)'\n";
echo "   - Utilisez ce mot de passe généré comme SMTP_PASSWORD\n";
echo "3. Pour les comptes sans authentification à deux facteurs :\n";
echo "   - Vous devez activer 'Accès moins sécurisé des applications'\n";
echo "   - Notez que Google peut désactiver cette option à tout moment\n";
echo "   - Il est recommandé d'activer l'authentification à deux facteurs et d'utiliser un mot de passe d'application\n";
echo "\nPour plus d'informations, consultez : https://support.google.com/accounts/answer/185833\n"; 
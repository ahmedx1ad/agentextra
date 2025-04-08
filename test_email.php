<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Utiliser la classe Mailer
use app\Helpers\Mailer;

// Email de destination pour le test
$to = "your-email@example.com"; // Remplacer par votre adresse email

// Contenu de l'email
$content = "
    <p>Ceci est un email de test pour vérifier que la fonctionnalité d'envoi d'emails fonctionne correctement.</p>
    <p>Si vous voyez cet email, cela signifie que tout est configuré correctement !</p>
";

// Bouton d'action pour le test
$buttons = [
    [
        'url' => base_url(),
        'text' => 'Visiter le site',
        'color' => '#28a745'
    ]
];

// Générer le template HTML
$body = Mailer::getTemplate('Test d\'envoi d\'email', $content, $buttons);

// Tenter d'envoyer l'email
try {
    $result = Mailer::send(
        $to,
        "Test d'envoi d'email - " . APP_NAME,
        $body
    );
    
    if ($result) {
        echo "L'email a été envoyé avec succès à {$to}.\n";
    } else {
        echo "Echec de l'envoi de l'email.\n";
    }
} catch (Exception $e) {
    echo "Une erreur est survenue : " . $e->getMessage() . "\n";
}

// Instructions pour le SMTP de Gmail
echo "\n\nNotes importantes pour l'utilisation de Gmail comme serveur SMTP :\n";
echo "1. Vous devez activer l'option 'Accès moins sécurisé aux applications' dans les paramètres de sécurité de votre compte Google,\n";
echo "   ou préférablement, créer un mot de passe d'application spécifique pour cette application.\n";
echo "2. Si vous utilisez la validation en deux étapes, vous devez générer un mot de passe d'application.\n";
echo "3. Configurez les constantes SMTP_HOST, SMTP_USERNAME et SMTP_PASSWORD dans app/Config/Constants.php.\n";
echo "\nPour plus d'informations, consultez : https://support.google.com/accounts/answer/185833\n"; 
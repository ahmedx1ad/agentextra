<?php
/**
 * Script pour standardiser l'affichage des notifications dans les vues
 * Ce script recherche les blocs de notification existants dans les vues
 * et les remplace par un bloc standardisé qui utilise un nouveau composant
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Standardisation des notifications dans les vues</h1>";

// Vérifier si le répertoire des vues existe
$viewsDir = __DIR__ . '/app/views';
if (!is_dir($viewsDir)) {
    die("<p style='color:red'>Le répertoire des vues n'existe pas!</p>");
}

// Créer le répertoire layouts s'il n'existe pas
$layoutsDir = $viewsDir . '/layouts';
if (!is_dir($layoutsDir)) {
    if (!mkdir($layoutsDir, 0755, true)) {
        die("<p style='color:red'>Impossible de créer le répertoire layouts!</p>");
    }
    echo "<p style='color:green'>Répertoire layouts créé avec succès.</p>";
}

// Liste des fichiers à traiter
$filesToProcess = [
    // Vues pour les agents
    'app/views/agents/index.php',
    'app/views/agents/create.php',
    'app/views/agents/edit.php',
    'app/views/agents/view.php',
    'app/views/agents/import.php',
    
    // Vues pour les services
    'app/views/services/index.php',
    'app/views/services/create.php',
    'app/views/services/edit.php',
    
    // Vues pour les responsables
    'app/views/responsables/index.php',
    'app/views/responsables/create.php',
    'app/views/responsables/edit.php',
    
    // Vues pour le tableau de bord
    'app/views/dashboard/index.php',
    
    // Vues pour les paramètres
    'app/views/settings/index.php',
    'app/views/settings/import.php',
    'app/views/settings/export.php',
    
    // Vues pour le profil
    'app/views/profile/index.php',
    
    // Vues pour l'authentification
    'app/views/auth/login.php',
    'app/views/auth/register.php',
    'app/views/auth/recover.php',
    'app/views/auth/reset.php',
    
    // Vues pour l'administration
    'app/views/admin/users.php',
    'app/views/admin/create_user.php',
    'app/views/admin/edit_user.php',
];

// Vérifier si le composant de notifications existe, sinon le créer
$notificationsComponentFile = $layoutsDir . '/notifications.php';
if (!file_exists($notificationsComponentFile)) {
    $notificationsContent = <<<'EOT'
<?php
/**
 * Composant de notification standardisé
 * Prend en charge à la fois les anciennes notifications basées sur session
 * et les nouvelles notifications via NotificationHelper
 */

// Essayer de charger NotificationHelper si disponible
$hasNotificationHelper = class_exists('\\App\\Helpers\\NotificationHelper');

// Messages de notification (compatibilité)
$successMessages = [];
$errorMessages = [];

// Récupérer les messages de l'ancien système (session)
if (isset($_SESSION['success'])) {
    $successMessages[] = [
        'message' => $_SESSION['success'],
        'details' => []
    ];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $errorMessages[] = [
        'message' => $_SESSION['error'],
        'details' => []
    ];
    unset($_SESSION['error']);
}

// Récupérer les messages du nouveau système si disponible
if ($hasNotificationHelper) {
    $messages = \App\Helpers\NotificationHelper::getMessages();
    $successMessages = array_merge($successMessages, $messages['success']);
    $errorMessages = array_merge($errorMessages, $messages['error']);
    
    // Effacer les messages après les avoir récupérés
    \App\Helpers\NotificationHelper::clearMessages();
}

// Afficher les messages de succès
if (!empty($successMessages)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php foreach ($successMessages as $msg): ?>
            <p><strong><i class="fa fa-check-circle"></i> Succès!</strong> <?= htmlspecialchars($msg['message']) ?></p>
            <?php if (!empty($msg['details'])): ?>
                <ul>
                    <?php foreach ($msg['details'] as $detail): ?>
                        <li><?= htmlspecialchars($detail) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif;

// Afficher les messages d'erreur
if (!empty($errorMessages)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php foreach ($errorMessages as $msg): ?>
            <p><strong><i class="fa fa-exclamation-circle"></i> Erreur!</strong> <?= htmlspecialchars($msg['message']) ?></p>
            <?php if (!empty($msg['details'])): ?>
                <ul>
                    <?php foreach ($msg['details'] as $detail): ?>
                        <li><?= htmlspecialchars($detail) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>
EOT;

    if (file_put_contents($notificationsComponentFile, $notificationsContent)) {
        echo "<p style='color:green'>Composant de notifications créé avec succès.</p>";
    } else {
        die("<p style='color:red'>Impossible de créer le composant de notifications!</p>");
    }
}

/**
 * Standardise les notifications dans un fichier
 * 
 * @param string $filePath Chemin du fichier à traiter
 * @return array Résultat de l'opération
 */
function standardizeNotificationsInFile($filePath) {
    echo "<h3>Traitement de " . basename($filePath) . "</h3>";
    
    // Vérifier si le fichier existe
    if (!file_exists($filePath)) {
        echo "<p style='color:orange'>⚠ Le fichier n'existe pas, passage au suivant.</p>";
        return [
            'success' => false,
            'message' => "Le fichier n'existe pas"
        ];
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Créer une sauvegarde
    file_put_contents($filePath . '.bak', $content);
    
    // Pattern 1: Recherche des blocs de notification basés sur session
    $patternSessionNotifications = '/<div\s+class="(?:alert alert-(?:success|danger)|notification (?:success|error)).*?"[^>]*>.*?'
        . '(?:\$_SESSION\[\'(?:success|error)\'\]|message).*?'
        . '<\/div>\s*?(?:<\/div>)?/is';

    // Pattern 2: Recherche d'autres motifs de notification
    $patternOtherNotifications = '/<div\s+class="(?:alert alert-(?:success|danger|warning|info)|notification (?:success|error|warning|info)).*?"[^>]*>.*?'
        . '<\/div>\s*?(?:<\/div>)?/is';

    // Bloc de remplacement standardisé
    $replacementBlock = "<?php include dirname(__DIR__) . '/layouts/notifications.php'; ?>\n";
    
    // Vérifier si le motif a été trouvé
    if (preg_match($patternSessionNotifications, $content)) {
        // Remplacer uniquement la première occurrence pour éviter les duplications
        $content = preg_replace($patternSessionNotifications, $replacementBlock, $content, 1);
        echo "<p style='color:green'>✓ Bloc de notification trouvé et remplacé.</p>";
        
        // Écrire le contenu mis à jour dans le fichier
        if (file_put_contents($filePath, $content)) {
            echo "<p style='color:green'>✓ Fichier mis à jour avec succès.</p>";
            return [
                'success' => true,
                'message' => "Notifications standardisées"
            ];
        } else {
            echo "<p style='color:red'>✗ Impossible d'écrire dans le fichier</p>";
            return [
                'success' => false,
                'message' => "Impossible d'écrire dans le fichier"
            ];
        }
    } else if (preg_match($patternOtherNotifications, $content)) {
        // Remplacer uniquement la première occurrence
        $content = preg_replace($patternOtherNotifications, $replacementBlock, $content, 1);
        echo "<p style='color:green'>✓ Autre bloc de notification trouvé et remplacé.</p>";
        
        // Écrire le contenu mis à jour dans le fichier
        if (file_put_contents($filePath, $content)) {
            echo "<p style='color:green'>✓ Fichier mis à jour avec succès.</p>";
            return [
                'success' => true,
                'message' => "Notifications standardisées (autre motif)"
            ];
        } else {
            echo "<p style='color:red'>✗ Impossible d'écrire dans le fichier</p>";
            return [
                'success' => false,
                'message' => "Impossible d'écrire dans le fichier"
            ];
        }
    } else {
        // Aucun bloc de notification trouvé, essayer d'ajouter le composant après la balise <body> ou <main>
        $bodyTagPattern = '/<body[^>]*>\s*/i';
        $mainTagPattern = '/<main[^>]*>\s*/i';
        $containerPattern = '/<div\s+class="(?:container|content)[^"]*"[^>]*>\s*/i';
        
        if (preg_match($bodyTagPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $content = substr_replace($content, $replacementBlock, $position, 0);
            echo "<p style='color:blue'>ℹ Aucun bloc trouvé, ajout après la balise &lt;body&gt;.</p>";
            
            // Écrire le contenu mis à jour dans le fichier
            if (file_put_contents($filePath, $content)) {
                echo "<p style='color:green'>✓ Fichier mis à jour avec succès.</p>";
                return [
                    'success' => true,
                    'message' => "Notifications ajoutées après la balise body"
                ];
            }
        } elseif (preg_match($mainTagPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $content = substr_replace($content, $replacementBlock, $position, 0);
            echo "<p style='color:blue'>ℹ Aucun bloc trouvé, ajout après la balise &lt;main&gt;.</p>";
            
            // Écrire le contenu mis à jour dans le fichier
            if (file_put_contents($filePath, $content)) {
                echo "<p style='color:green'>✓ Fichier mis à jour avec succès.</p>";
                return [
                    'success' => true,
                    'message' => "Notifications ajoutées après la balise main"
                ];
            }
        } elseif (preg_match($containerPattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);
            $content = substr_replace($content, $replacementBlock, $position, 0);
            echo "<p style='color:blue'>ℹ Aucun bloc trouvé, ajout après le conteneur principal.</p>";
            
            // Écrire le contenu mis à jour dans le fichier
            if (file_put_contents($filePath, $content)) {
                echo "<p style='color:green'>✓ Fichier mis à jour avec succès.</p>";
                return [
                    'success' => true,
                    'message' => "Notifications ajoutées après le conteneur"
                ];
            }
        } else {
            echo "<p style='color:orange'>⚠ Aucun bloc de notification trouvé et impossible de trouver un emplacement approprié pour l'ajouter.</p>";
            return [
                'success' => false,
                'message' => "Aucun emplacement approprié trouvé"
            ];
        }
    }
    
    echo "<p style='color:red'>✗ Erreur lors de la mise à jour du fichier</p>";
    return [
        'success' => false,
        'message' => "Erreur lors de la mise à jour"
    ];
}

// Compteurs
$totalProcessed = 0;
$totalStandardized = 0;
$results = [];

// Traiter chaque fichier
foreach ($filesToProcess as $file) {
    $result = standardizeNotificationsInFile($file);
    $results[$file] = $result;
    
    $totalProcessed++;
    if ($result['success']) {
        $totalStandardized++;
    }
}

// Afficher le résumé
echo "<h2>Résumé</h2>";
echo "<p>Total de fichiers traités: $totalProcessed</p>";
echo "<p>Total de fichiers standardisés: $totalStandardized</p>";

echo "<h2>Résultats détaillés</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Fichier</th><th>État</th><th>Message</th></tr>";

foreach ($results as $file => $result) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($file) . "</td>";
    echo "<td>" . ($result['success'] ? "<span style='color:green'>Succès</span>" : "<span style='color:red'>Échec</span>") . "</td>";
    echo "<td>" . htmlspecialchars($result['message']) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Prochaines étapes
echo "<h2>Prochaines étapes</h2>";
echo "<ol>";
echo "<li>Exécuter le script pour standardiser les méthodes de notification dans les contrôleurs.</li>";
echo "<li>Vérifier que les notifications s'affichent correctement sur toutes les pages.</li>";
echo "<li>Ajouter manuellement le composant de notification aux pages qui n'ont pas pu être automatiquement standardisées.</li>";
echo "</ol>";

echo "<p><a href='enhance_controllers.php' class='btn btn-primary'>Standardiser les contrôleurs</a></p>";
echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>"; 
<?php
/**
 * Script pour standardiser les notifications et redirections dans les contrôleurs
 * Ce script recherche les motifs de notification via session ($_SESSION['success/error']) 
 * et les remplace par des appels à NotificationHelper
 */

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Amélioration des contrôleurs avec NotificationHelper</h1>";

// Vérifier si le répertoire des contrôleurs existe
$controllersDir = __DIR__ . '/app/Controllers';
if (!is_dir($controllersDir)) {
    die("<p style='color:red'>Le répertoire des contrôleurs n'existe pas!</p>");
}

// Vérifier si NotificationHelper.php existe, sinon le créer
$helpersDir = __DIR__ . '/app/Helpers';
if (!is_dir($helpersDir)) {
    if (!mkdir($helpersDir, 0755, true)) {
        die("<p style='color:red'>Impossible de créer le répertoire des helpers!</p>");
    }
    echo "<p style='color:green'>Répertoire des helpers créé avec succès.</p>";
}

$notificationHelperFile = $helpersDir . '/NotificationHelper.php';
if (!file_exists($notificationHelperFile)) {
    $notificationHelperContent = <<<'EOT'
<?php
namespace App\Helpers;

/**
 * Helper pour gérer les notifications d'une manière standardisée
 */
class NotificationHelper {
    private static $messages = [
        'success' => [],
        'error' => []
    ];

    /**
     * Ajoute un message de succès
     * 
     * @param string $message Le message à afficher
     * @param array $details Détails optionnels (liste de messages supplémentaires)
     * @return void
     */
    public static function success(string $message, array $details = []): void {
        self::$messages['success'][] = [
            'message' => $message,
            'details' => $details
        ];
        
        // Garder la compatibilité avec l'ancien système
        $_SESSION['success'] = $message;
    }

    /**
     * Ajoute un message d'erreur
     * 
     * @param string $message Le message à afficher
     * @param array $details Détails optionnels (liste de messages supplémentaires)
     * @return void
     */
    public static function error(string $message, array $details = []): void {
        self::$messages['error'][] = [
            'message' => $message,
            'details' => $details
        ];
        
        // Garder la compatibilité avec l'ancien système
        $_SESSION['error'] = $message;
    }

    /**
     * Récupère tous les messages
     * 
     * @return array Tableau associatif contenant les messages 'success' et 'error'
     */
    public static function getMessages(): array {
        return self::$messages;
    }

    /**
     * Efface tous les messages
     * 
     * @return void
     */
    public static function clearMessages(): void {
        self::$messages = [
            'success' => [],
            'error' => []
        ];
    }

    /**
     * Ajoute un message de succès et redirige
     * 
     * @param string $message Le message à afficher
     * @param string $url L'URL vers laquelle rediriger
     * @param bool $withExit Appeler exit() après la redirection
     * @return void
     */
    public static function successAndRedirect(string $message, string $url, bool $withExit = true): void {
        self::success($message);
        redirect($url, $withExit);
    }

    /**
     * Ajoute un message d'erreur et redirige
     * 
     * @param string $message Le message à afficher
     * @param string $url L'URL vers laquelle rediriger
     * @param bool $withExit Appeler exit() après la redirection
     * @return void
     */
    public static function errorAndRedirect(string $message, string $url, bool $withExit = true): void {
        self::error($message);
        redirect($url, $withExit);
    }
}
EOT;

    if (file_put_contents($notificationHelperFile, $notificationHelperContent)) {
        echo "<p style='color:green'>Helper de notification créé avec succès.</p>";
    } else {
        die("<p style='color:red'>Impossible de créer le helper de notification!</p>");
    }
}

// Liste des contrôleurs à améliorer
$controllerFiles = [
    'AgentController.php',
    'ServiceController.php',
    'ResponsableController.php',
    'UserController.php',
    'SettingsController.php',
    'AuthController.php',
    'DashboardController.php',
    'ProfileController.php',
    'ExportController.php',
    'CacheController.php'
];

// Fonction pour améliorer un contrôleur
function enhanceController($filePath) {
    echo "<h3>Traitement de " . basename($filePath) . "</h3>";
    
    // Vérifier si le fichier existe
    if (!file_exists($filePath)) {
        echo "<p style='color:orange'>⚠ Le fichier n'existe pas, passage au suivant.</p>";
        return [
            'success' => false,
            'message' => "Le fichier n'existe pas",
            'modifications' => 0
        ];
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Créer une sauvegarde
    file_put_contents($filePath . '.bak', $content);
    
    // Vérifier si le NotificationHelper est importé
    $hasHelperImport = preg_match('/use\s+App\\\\Helpers\\\\NotificationHelper/i', $content);
    
    // Si l'import n'existe pas, l'ajouter après les autres imports
    if (!$hasHelperImport) {
        // Chercher le dernier import ou le namespace
        if (preg_match('/^(.*?)(use [^;]+;[^\n]*\n\s*)+/sm', $content, $matches)) {
            $position = strlen($matches[0]);
            $content = substr($content, 0, $position) . "use App\\Helpers\\NotificationHelper;\n\n" . substr($content, $position);
            echo "<p style='color:blue'>ℹ Import NotificationHelper ajouté.</p>";
        } elseif (preg_match('/^(.*?)(namespace [^;]+;[^\n]*\n\s*)/sm', $content, $matches)) {
            $position = strlen($matches[0]);
            $content = substr($content, 0, $position) . "use App\\Helpers\\NotificationHelper;\n\n" . substr($content, $position);
            echo "<p style='color:blue'>ℹ Import NotificationHelper ajouté après le namespace.</p>";
        } else {
            echo "<p style='color:orange'>⚠ Impossible de trouver où ajouter l'import NotificationHelper.</p>";
        }
    }
    
    // Compter les modifications
    $totalModifications = 0;
    
    // Motifs pour les remplacements success/error + redirect
    
    // 1. Motif pour $_SESSION['success'] = "message"; redirect('url'); (cas le plus courant)
    $successRedirectPattern = '/\$_SESSION\[\'success\'\]\s*=\s*"([^"]*)"\s*;\s*redirect\(\s*[\'"]([^\'"]*)[\'"](?:,\s*true)?\s*\)\s*;/';
    $content = preg_replace_callback($successRedirectPattern, function($matches) use (&$totalModifications) {
        $totalModifications++;
        return 'NotificationHelper::successAndRedirect("' . $matches[1] . '", "' . $matches[2] . '");';
    }, $content);
    
    // 2. Motif pour $_SESSION['error'] = "message"; redirect('url'); (cas le plus courant)
    $errorRedirectPattern = '/\$_SESSION\[\'error\'\]\s*=\s*"([^"]*)"\s*;\s*redirect\(\s*[\'"]([^\'"]*)[\'"](?:,\s*true)?\s*\)\s*;/';
    $content = preg_replace_callback($errorRedirectPattern, function($matches) use (&$totalModifications) {
        $totalModifications++;
        return 'NotificationHelper::errorAndRedirect("' . $matches[1] . '", "' . $matches[2] . '");';
    }, $content);
    
    // 3. Motif pour $_SESSION['success'] seul sans redirection
    $successPattern = '/\$_SESSION\[\'success\'\]\s*=\s*"([^"]*)"\s*;/';
    $content = preg_replace_callback($successPattern, function($matches) use (&$totalModifications) {
        $totalModifications++;
        return 'NotificationHelper::success("' . $matches[1] . '");';
    }, $content);
    
    // 4. Motif pour $_SESSION['error'] seul sans redirection
    $errorPattern = '/\$_SESSION\[\'error\'\]\s*=\s*"([^"]*)"\s*;/';
    $content = preg_replace_callback($errorPattern, function($matches) use (&$totalModifications) {
        $totalModifications++;
        return 'NotificationHelper::error("' . $matches[1] . '");';
    }, $content);
    
    // Si aucune modification n'a été faite, conserver le fichier original
    if ($content === $originalContent) {
        echo "<p style='color:blue'>ℹ Aucune modification nécessaire.</p>";
        return [
            'success' => true,
            'message' => "Aucune modification nécessaire",
            'modifications' => 0
        ];
    }
    
    // Écrire le contenu mis à jour dans le fichier
    if (file_put_contents($filePath, $content)) {
        echo "<p style='color:green'>✓ $totalModifications modifications effectuées avec succès</p>";
        return [
            'success' => true,
            'message' => "$totalModifications modifications effectuées avec succès",
            'modifications' => $totalModifications
        ];
    } else {
        echo "<p style='color:red'>✗ Impossible d'écrire dans le fichier</p>";
        return [
            'success' => false,
            'message' => "Impossible d'écrire dans le fichier",
            'modifications' => 0
        ];
    }
}

// Compteurs
$totalProcessed = 0;
$totalEnhanced = 0;
$totalModifications = 0;
$results = [];

// Traiter chaque fichier
foreach ($controllerFiles as $file) {
    $filePath = $controllersDir . '/' . $file;
    $result = enhanceController($filePath);
    $results[$file] = $result;
    
    $totalProcessed++;
    if ($result['success'] && $result['modifications'] > 0) {
        $totalEnhanced++;
        $totalModifications += $result['modifications'];
    }
}

// Afficher le résumé
echo "<h2>Résumé</h2>";
echo "<p>Total de contrôleurs traités: $totalProcessed</p>";
echo "<p>Total de contrôleurs améliorés: $totalEnhanced</p>";
echo "<p>Total de modifications: $totalModifications</p>";

echo "<h2>Résultats détaillés</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Contrôleur</th><th>État</th><th>Message</th><th>Modifications</th></tr>";

foreach ($results as $file => $result) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($file) . "</td>";
    echo "<td>" . ($result['success'] ? "<span style='color:green'>Succès</span>" : "<span style='color:red'>Échec</span>") . "</td>";
    echo "<td>" . htmlspecialchars($result['message']) . "</td>";
    echo "<td>" . $result['modifications'] . "</td>";
    echo "</tr>";
}

echo "</table>";

// Prochaines étapes
echo "<h2>Prochaines étapes</h2>";
echo "<ol>";
echo "<li>Si certains contrôleurs n'ont pas pu être automatiquement standardisés, effectuez les modifications manuellement :</li>";
echo "</ol>";

echo "<pre>";
echo "// 1. Importer le helper en haut du fichier\n";
echo "use App\\Helpers\\NotificationHelper;\n\n";
echo "// 2. Remplacer\n";
echo "\$_SESSION['success'] = \"Message de succès\"; redirect('url');\n\n";
echo "// Par\n";
echo "NotificationHelper::successAndRedirect(\"Message de succès\", 'url');\n\n";
echo "// Et remplacer\n";
echo "\$_SESSION['error'] = \"Message d'erreur\"; redirect('url');\n\n";
echo "// Par\n";
echo "NotificationHelper::errorAndRedirect(\"Message d'erreur\", 'url');\n";
echo "</pre>";

echo "<p><a href='index.php' class='btn btn-primary'>Retour à l'accueil</a></p>"; 
<?php
/**
 * Script de correction pour les problèmes de langue et notifications
 * Ce script modifie automatiquement les fichiers nécessaires pour:
 * 1. Corriger le problème de changement de langue en arabe
 * 2. Ajouter une notification lors de la sauvegarde des paramètres
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Correction des paramètres AgentExtra</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #2c3e50; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        button, .button { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <h1>Correction des paramètres de langue et notifications</h1>
";

// Fonction pour afficher le statut
function status($message, $type = 'info') {
    $icon = $type === 'success' ? '✓' : ($type === 'error' ? '✗' : 'ℹ');
    echo "<p class='{$type}'>{$icon} {$message}</p>";
}

// 1. Modifier bootstrap.php pour corriger les sessions
echo "<h2>1. Correction de la configuration des cookies de session</h2>";

$bootstrapFile = __DIR__ . '/bootstrap.php';
if (file_exists($bootstrapFile)) {
    $bootstrapContent = file_get_contents($bootstrapFile);
    
    // Vérifier si la ligne existe déjà
    if (strpos($bootstrapContent, "ini_set('session.cookie_secure', '1');") !== false) {
        // Remplacer la configuration pour environnement local
        $bootstrapContent = str_replace(
            "ini_set('session.cookie_secure', '1');", 
            "ini_set('session.cookie_secure', '0'); // Modifié pour environnement local", 
            $bootstrapContent
        );
        
        if (file_put_contents($bootstrapFile, $bootstrapContent)) {
            status("Configuration des cookies de session corrigée pour environnement local", 'success');
        } else {
            status("Impossible de modifier le fichier bootstrap.php. Vérifiez les permissions.", 'error');
        }
    } else {
        status("La configuration des cookies de session a déjà été modifiée ou utilise un format différent", 'warning');
    }
} else {
    status("Le fichier bootstrap.php n'existe pas!", 'error');
}

// 2. Modifier SettingsController.php pour ajouter la notification
echo "<h2>2. Ajout de notification lors du changement de langue</h2>";

$controllerFile = __DIR__ . '/app/Controllers/SettingsController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    // Rechercher la méthode changeLanguage
    if (preg_match('/public function changeLanguage\([^)]*\): void \{([^}]+)\}/s', $controllerContent, $matches)) {
        $methodBody = $matches[1];
        
        // Vérifier si la notification existe déjà
        if (strpos($methodBody, 'NotificationHelper::success') === false) {
            // Ajouter la notification avant la redirection
            $newMethodBody = preg_replace(
                '/(redirect\(\'settings\?tab=localization\'\);)/i',
                '// Ajouter une notification de succès
        NotificationHelper::success(
            "Langue modifiée",
            ["La langue a été modifiée avec succès en " . ucfirst($lang) . "."]
        );
        
        $1',
                $methodBody
            );
            
            // Remplacer l'ancienne méthode par la nouvelle
            $newControllerContent = str_replace($methodBody, $newMethodBody, $controllerContent);
            
            if (file_put_contents($controllerFile, $newControllerContent)) {
                status("Notification de succès ajoutée à la méthode changeLanguage", 'success');
            } else {
                status("Impossible de modifier le fichier SettingsController.php. Vérifiez les permissions.", 'error');
            }
        } else {
            status("La notification existe déjà dans la méthode changeLanguage", 'warning');
        }
    } else {
        status("Impossible de trouver la méthode changeLanguage dans SettingsController.php", 'error');
        
        // Proposer un correctif manuel
        echo "<div class='box'>";
        echo "<h3>Modification manuelle de SettingsController.php</h3>";
        echo "<p>Ajoutez ce code avant la ligne <code>redirect('settings?tab=localization');</code> dans la méthode <code>changeLanguage</code> :</p>";
        echo "<pre>
        // Ajouter une notification de succès
        NotificationHelper::success(
            \"Langue modifiée\",
            [\"La langue a été modifiée avec succès en \" . ucfirst(\$lang) . \".\"]
        );
        </pre>";
        echo "</div>";
    }
} else {
    status("Le fichier SettingsController.php n'existe pas!", 'error');
}

// 3. Vérifier le dossier et les fichiers de langue
echo "<h2>3. Vérification des fichiers de langue</h2>";

$langDir = __DIR__ . '/app/lang';
if (!is_dir($langDir)) {
    status("Le dossier des langues n'existe pas!", 'error');
    
    // Créer le dossier
    if (mkdir($langDir, 0755, true)) {
        status("Dossier des langues créé avec succès", 'success');
    } else {
        status("Impossible de créer le dossier des langues", 'error');
    }
} else {
    status("Le dossier des langues existe", 'success');
}

// Vérifier les fichiers de langue
$langFiles = [
    'fr.php' => 'Fichier de langue française',
    'en.php' => 'Fichier de langue anglaise',
    'ar.php' => 'Fichier de langue arabe'
];

foreach ($langFiles as $file => $description) {
    $filePath = $langDir . '/' . $file;
    if (!file_exists($filePath)) {
        status("Le fichier {$file} n'existe pas!", 'error');
        
        // Créer un contenu par défaut selon la langue
        $content = "<?php\n/**\n * {$description}\n */\nreturn [\n    // Contenu par défaut\n    'app_name' => 'AgentExtra',\n];\n";
        
        if (file_put_contents($filePath, $content)) {
            status("Fichier {$file} créé avec un contenu minimal", 'success');
        } else {
            status("Impossible de créer le fichier {$file}", 'error');
        }
    } else {
        status("Le fichier {$file} existe", 'success');
    }
}

// 4. Proposer des solutions alternatives
echo "<h2>4. Solutions alternatives</h2>";

echo "<div class='box'>";
echo "<h3>Alternative 1: Utiliser un script de redirection</h3>";
echo "<p>Si les modifications ci-dessus ne résolvent pas le problème, vous pouvez créer un script de redirection :</p>";
echo "<pre>
// Créez un fichier 'change_lang.php' avec ce contenu :
&lt;?php
session_start();
\$lang = isset(\$_GET['lang']) ? \$_GET['lang'] : 'fr';
if (in_array(\$lang, ['fr', 'en', 'ar'])) {
    \$_SESSION['user_language'] = \$lang;
    // Rediriger vers la page précédente ou la page d'accueil
    \$redirect = isset(\$_SERVER['HTTP_REFERER']) ? \$_SERVER['HTTP_REFERER'] : 'settings';
    header('Location: ' . \$redirect);
    exit;
}
</pre>";
echo "<p>Puis utilisez des liens comme : <code>&lt;a href=\"change_lang.php?lang=ar\">العربية&lt;/a></code></p>";
echo "</div>";

echo "<div class='box'>";
echo "<h3>Alternative 2: Ajouter un contrôleur direct pour changer la langue</h3>";
echo "<pre>
// Ajoutez cette méthode au SettingsController.php

/**
 * Méthode simplifiée pour changer la langue via GET
 */
public function setLang(): void {
    // Récupérer la langue demandée
    \$lang = filter_input(INPUT_GET, 'lang', FILTER_SANITIZE_STRING);
    
    if (!\$lang || !in_array(\$lang, ['fr', 'en', 'ar'])) {
        \$lang = 'fr';
    }
    
    // Stocker la langue dans la session
    \$_SESSION['user_language'] = \$lang;
    
    // Ajouter une notification
    NotificationHelper::success(
        \"Langue modifiée\",
        [\"La langue a été modifiée avec succès en \" . ucfirst(\$lang) . \".\"]
    );
    
    // Rediriger vers la page précédente ou les paramètres
    \$redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL) ?: 'settings';
    redirect(\$redirect);
    exit;
}
</pre>";
echo "<p>Puis ajoutez une route dans index.php : <code>'settings/set-lang' => ['controller' => 'SettingsController', 'action' => 'setLang']</code></p>";
echo "</div>";

// 5. Ajout d'un script de test et diagnostic
echo "<h2>5. Script de test des sessions</h2>";

echo "<div class='box'>";
echo "<p>Voici un script simple pour tester le fonctionnement des sessions :</p>";

// Créer le fichier de test
$testFile = __DIR__ . '/test_session.php';
$testContent = <<<'EOT'
<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

echo "<h1>Test des sessions</h1>";

// Afficher l'ID de session
echo "<p>ID de session: " . session_id() . "</p>";

// Afficher les paramètres de session actuels
echo "<h2>Configuration des sessions</h2>";
echo "<pre>";
echo "session.save_path: " . ini_get('session.save_path') . "\n";
echo "session.cookie_path: " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain: " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure: " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly: " . ini_get('session.cookie_httponly') . "\n";
echo "session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n";
echo "</pre>";

// Afficher le contenu actuel de la session
echo "<h2>Contenu de la session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Permettre de définir une valeur de test
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key'], $_POST['value'])) {
    $_SESSION[$_POST['key']] = $_POST['value'];
    echo "<p style='color:green'>Valeur définie : " . htmlspecialchars($_POST['key']) . " = " . htmlspecialchars($_POST['value']) . "</p>";
}

// Permettre de changer la langue
if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en', 'ar'])) {
    $_SESSION['user_language'] = $_GET['lang'];
    echo "<p style='color:green'>Langue changée en : " . htmlspecialchars($_GET['lang']) . "</p>";
    echo "<script>setTimeout(function() { window.location.href = 'test_session.php'; }, 1000);</script>";
    exit;
}

// Formulaire pour définir une valeur
echo "<h2>Définir une valeur de session</h2>";
echo "<form method='post'>";
echo "  <label>Clé: <input type='text' name='key' value='test_key'></label><br>";
echo "  <label>Valeur: <input type='text' name='value' value='test_value'></label><br>";
echo "  <button type='submit'>Définir</button>";
echo "</form>";

// Liens pour changer la langue
echo "<h2>Changer la langue</h2>";
echo "<p>";
echo "<a href='?lang=fr' style='margin-right: 10px;'>Français</a> ";
echo "<a href='?lang=en' style='margin-right: 10px;'>English</a> ";
echo "<a href='?lang=ar'>العربية</a>";
echo "</p>";

// Lien pour effacer la session
echo "<p><a href='?clear=1'>Effacer la session</a></p>";

// Effacer la session si demandé
if (isset($_GET['clear'])) {
    session_unset();
    session_destroy();
    echo "<p style='color:orange'>Session effacée</p>";
    echo "<script>setTimeout(function() { window.location.href = 'test_session.php'; }, 1000);</script>";
    exit;
}
EOT;

file_put_contents($testFile, $testContent);
echo "<p class='success'>✓ Script de test créé : <a href='test_session.php' target='_blank'>test_session.php</a></p>";
echo "<p>Utilisez ce script pour vérifier si les sessions fonctionnent correctement et si les valeurs sont persistantes.</p>";
echo "</div>";

// Proposer des liens pour continuer
echo "<h2>Actions à effectuer</h2>";
echo "<div style='margin-top: 20px;'>";
echo "<p><a href='settings' class='button'>Aller à la page des paramètres</a></p>";
echo "<p><a href='test_session.php' class='button'>Tester les sessions</a></p>";
echo "<p><a href='fix_language_settings.php' class='button'>Utiliser l'outil de diagnostic complet</a></p>";
echo "</div>";

echo "</body></html>"; 
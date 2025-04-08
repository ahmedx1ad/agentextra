<?php
// Script de diagnostic et réparation pour le problème de changement de langue
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic du système de langues</h1>";

// 1. Vérifier les informations de session
echo "<h2>1. Informations de session</h2>";
session_start();

echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session path: " . session_save_path() . "</p>";
echo "<p>Cookies enabled: " . (isset($_COOKIE[session_name()]) ? 'Oui' : 'Non') . "</p>";

echo "<h3>Contenu de la session:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Vérifier les fichiers de langue disponibles
echo "<h2>2. Fichiers de langue disponibles</h2>";
$langDir = __DIR__ . '/app/lang';
echo "<p>Dossier des langues: $langDir</p>";

if (is_dir($langDir)) {
    echo "<p style='color:green'>✓ Le dossier des langues existe</p>";
    $langFiles = glob($langDir . '/*.php');
    if (!empty($langFiles)) {
        echo "<p>Fichiers trouvés:</p><ul>";
        foreach ($langFiles as $file) {
            echo "<li>" . basename($file) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ Aucun fichier de langue trouvé!</p>";
    }
} else {
    echo "<p style='color:red'>✗ Le dossier des langues n'existe pas!</p>";
}

// 3. Tester le changement de langue
echo "<h2>3. Test de changement de langue</h2>";

// Afficher la langue actuelle
$currentLang = $_SESSION['user_language'] ?? 'fr';
echo "<p>Langue actuelle: <strong>$currentLang</strong></p>";

// Option pour changer la langue
echo "<form method='post'>";
echo "<select name='lang'>";
echo "<option value='fr'" . ($currentLang == 'fr' ? " selected" : "") . ">Français</option>";
echo "<option value='en'" . ($currentLang == 'en' ? " selected" : "") . ">English</option>";
echo "<option value='ar'" . ($currentLang == 'ar' ? " selected" : "") . ">العربية</option>";
echo "</select>";
echo " <button type='submit'>Changer la langue</button>";
echo "</form>";

// Traiter le changement de langue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
    $newLang = filter_input(INPUT_POST, 'lang', FILTER_SANITIZE_STRING);
    if (in_array($newLang, ['fr', 'en', 'ar'])) {
        $_SESSION['user_language'] = $newLang;
        echo "<p style='color:green'>✓ Langue changée en: <strong>$newLang</strong></p>";
        echo "<p>Rafraîchissement dans 2 secondes...</p>";
        echo "<script>setTimeout(function() { window.location.reload(); }, 2000);</script>";
    }
}

// 4. Tester l'affichage du texte en arabe
echo "<h2>4. Test d'affichage</h2>";

echo "<div style='margin-top: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";
echo "<h3>Exemple de texte en arabe:</h3>";
echo "<p dir='rtl' lang='ar'>مرحبا بكم في تطبيق AgentExtra</p>";
echo "<p dir='rtl' lang='ar'>هذا هو اختبار للغة العربية</p>";
echo "</div>";

// 5. Actions possibles
echo "<h2>5. Actions</h2>";

echo "<div style='margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<p>Options de réparation:</p>";
echo "<ul>";
echo "<li><a href='?action=verify'>Vérifier l'intégrité du système de langues</a></li>";
echo "<li><a href='?action=repair'>Réparer le système de langues</a></li>";
echo "<li><a href='?action=clear_session'>Effacer les données de session</a></li>";
echo "<li><a href='settings/language/ar'>Essayer de changer en arabe via l'URL</a></li>";
echo "</ul>";
echo "</div>";

// 6. Traiter les actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    echo "<h2>Exécution de l'action: " . htmlspecialchars($action) . "</h2>";
    
    switch ($action) {
        case 'verify':
            echo "<p>Vérification de l'intégrité du système de langues...</p>";
            if (file_exists(__DIR__ . '/bootstrap.php')) {
                echo "<p style='color:green'>✓ Le fichier bootstrap.php existe</p>";
                
                if (file_exists(__DIR__ . '/app/helpers/TranslationHelper.php')) {
                    echo "<p style='color:green'>✓ Le helper de traduction existe</p>";
                } else {
                    echo "<p style='color:red'>✗ Le helper de traduction est manquant!</p>";
                }
            } else {
                echo "<p style='color:red'>✗ Le fichier bootstrap.php est manquant!</p>";
            }
            break;
            
        case 'repair':
            echo "<p>Réparation du système de langues...</p>";
            
            // Forcer la création du dossier des langues
            if (!is_dir($langDir)) {
                mkdir($langDir, 0755, true);
                echo "<p>Dossier des langues créé: $langDir</p>";
            }
            
            // Vérifier si les fichiers de langue existent
            $langFiles = ['fr.php', 'en.php', 'ar.php'];
            foreach ($langFiles as $file) {
                $filePath = $langDir . '/' . $file;
                if (!file_exists($filePath)) {
                    echo "<p>Création du fichier de langue manquant: $file</p>";
                    
                    // Contenu par défaut
                    $content = "<?php\n/**\n * Fichier de langue {$file}\n */\nreturn [\n    'app_name' => 'AgentExtra',\n    'dashboard' => 'Tableau de bord',\n];\n";
                    file_put_contents($filePath, $content);
                }
            }
            
            // Réinitialiser la langue en session
            $_SESSION['user_language'] = 'fr';
            echo "<p>Langue en session réinitialisée à 'fr'</p>";
            
            echo "<p style='color:green'>✓ Réparation terminée!</p>";
            echo "<p>Essayez maintenant de changer la langue depuis le menu.</p>";
            break;
            
        case 'clear_session':
            // Effacer les données de session
            session_unset();
            session_destroy();
            echo "<p style='color:green'>✓ Session effacée!</p>";
            echo "<p>Rafraîchissement dans 2 secondes...</p>";
            echo "<script>setTimeout(function() { window.location = 'language_fix.php'; }, 2000);</script>";
            break;
    }
} 
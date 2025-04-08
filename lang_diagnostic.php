<?php
/**
 * Diagnostic des problèmes de langue
 * Ce script vérifie les problèmes potentiels avec le système de traduction
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
if (file_exists(__DIR__ . '/bootstrap.php')) {
    require_once __DIR__ . '/bootstrap.php';
    $bootstrap_loaded = true;
} else {
    $bootstrap_loaded = false;
}

// Fonction d'échappement HTML sécurisée
function safe_html($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Styles CSS et en-tête HTML
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic des langues</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
        h1 { color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        h2 { color: #3498db; margin-top: 20px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .language-switcher { margin: 20px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; }
        .language-switcher a { margin-right: 10px; padding: 5px 10px; text-decoration: none; border: 1px solid #ddd; border-radius: 3px; }
        .language-switcher a:hover { background: #e9e9e9; }
        .language-switcher a.active { background: #3498db; color: white; border-color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Diagnostic du système de langues</h1>
HTML;

// Tester le changement de langue via GET
if (isset($_GET['set_lang'])) {
    $_SESSION['user_language'] = $_GET['set_lang'];
    echo '<div class="success">Langue changée en ' . safe_html($_GET['set_lang']) . '</div>';
    echo '<script>setTimeout(function() { window.location = "lang_diagnostic.php"; }, 1000);</script>';
    exit;
}

// 1. Vérifier si la session fonctionne
echo '<h2>1. Vérification de la session</h2>';
if (!isset($_SESSION)) {
    echo '<div class="error">ERREUR: La session n\'est pas démarrée</div>';
} else {
    echo '<div class="success">La session fonctionne correctement</div>';
    
    // Afficher les informations de session
    echo '<div class="box">';
    echo '<h3>Variables de session</h3>';
    echo '<table>';
    echo '<tr><th>Clé</th><th>Valeur</th></tr>';
    
    foreach ($_SESSION as $key => $value) {
        echo '<tr>';
        echo '<td>' . safe_html($key) . '</td>';
        echo '<td>' . (is_array($value) ? 'Array' : safe_html((string)$value)) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</div>';
    
    // Si langue pas définie dans la session, l'ajouter
    if (!isset($_SESSION['user_language'])) {
        $_SESSION['user_language'] = 'fr';
        echo '<div class="warning">Langue par défaut (fr) définie dans la session</div>';
    }
}

// 2. Vérifier la configuration des cookies
echo '<h2>2. Vérification des cookies</h2>';
if (isset($_SERVER['HTTP_COOKIE'])) {
    echo '<div class="success">Les cookies sont activés</div>';
    
    echo '<div class="box">';
    echo '<h3>Cookies actuels</h3>';
    echo '<pre>' . safe_html($_SERVER['HTTP_COOKIE']) . '</pre>';
    echo '</div>';
} else {
    echo '<div class="warning">Aucun cookie détecté, cela peut causer des problèmes de persistance de session</div>';
}

// Afficher la configuration PHP pour les cookies et sessions
echo '<div class="box">';
echo '<h3>Configuration PHP des sessions/cookies</h3>';
echo '<table>';
$sessionParams = [
    'session.save_path', 'session.name', 'session.cookie_lifetime', 
    'session.cookie_path', 'session.cookie_domain', 'session.cookie_secure',
    'session.cookie_httponly', 'session.cookie_samesite', 'session.use_strict_mode',
    'session.use_cookies', 'session.use_only_cookies', 'session.gc_maxlifetime'
];

foreach ($sessionParams as $param) {
    echo '<tr>';
    echo '<td>' . $param . '</td>';
    echo '<td>' . ini_get($param) . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '</div>';

// 3. Vérifier les fichiers de langue
echo '<h2>3. Vérification des fichiers de langue</h2>';

$langDir = __DIR__ . '/app/lang';
if (!is_dir($langDir)) {
    echo '<div class="error">Le dossier de langue n\'existe pas: ' . safe_html($langDir) . '</div>';
    
    // Proposer de créer le dossier
    echo '<div class="box">';
    echo '<h3>Créer le dossier de langue</h3>';
    echo '<form method="post">';
    echo '<input type="hidden" name="create_lang_dir" value="1">';
    echo '<button type="submit">Créer le dossier app/lang</button>';
    echo '</form>';
    echo '</div>';
} else {
    echo '<div class="success">Le dossier de langue existe: ' . safe_html($langDir) . '</div>';
    
    // Vérifier les fichiers de langue disponibles
    $langFiles = glob($langDir . '/*.php');
    
    if (empty($langFiles)) {
        echo '<div class="warning">Aucun fichier de langue trouvé dans le dossier</div>';
    } else {
        echo '<div class="success">' . count($langFiles) . ' fichiers de langue trouvés</div>';
        
        echo '<div class="box">';
        echo '<h3>Fichiers de langue disponibles</h3>';
        echo '<table>';
        echo '<tr><th>Fichier</th><th>Taille</th><th>Modification</th><th>Nombre de traductions</th></tr>';
        
        foreach ($langFiles as $file) {
            echo '<tr>';
            echo '<td>' . safe_html(basename($file)) . '</td>';
            echo '<td>' . filesize($file) . ' octets</td>';
            echo '<td>' . date('Y-m-d H:i:s', filemtime($file)) . '</td>';
            
            // Compter les traductions
            $translations = @include $file;
            $count = is_array($translations) ? count($translations) : 0;
            echo '<td>' . $count . '</td>';
            
            echo '</tr>';
        }
        
        echo '</table>';
        echo '</div>';
    }
    
    // Vérifier si les fichiers de langue de base existent
    $requiredLangs = ['fr.php', 'en.php', 'ar.php'];
    $missingLangs = [];
    
    foreach ($requiredLangs as $lang) {
        if (!file_exists($langDir . '/' . $lang)) {
            $missingLangs[] = $lang;
        }
    }
    
    if (!empty($missingLangs)) {
        echo '<div class="warning">Fichiers de langue manquants: ' . implode(', ', $missingLangs) . '</div>';
        
        // Proposer de créer les fichiers manquants
        echo '<div class="box">';
        echo '<h3>Créer les fichiers de langue manquants</h3>';
        echo '<form method="post">';
        foreach ($missingLangs as $lang) {
            echo '<input type="checkbox" name="create_lang[]" value="' . safe_html($lang) . '" checked> ' . safe_html($lang) . '<br>';
        }
        echo '<button type="submit">Créer les fichiers sélectionnés</button>';
        echo '</form>';
        echo '</div>';
    }
}

// 4. Tester le système de traduction
echo '<h2>4. Test du système de traduction</h2>';

if ($bootstrap_loaded && class_exists('app\Helpers\TranslationHelper')) {
    echo '<div class="success">Le helper de traduction est disponible</div>';
    
    // Test de traduction
    echo '<div class="box">';
    echo '<h3>Test de traduction</h3>';
    
    $currentLang = $_SESSION['user_language'] ?? 'fr';
    app\Helpers\TranslationHelper::init($currentLang);
    
    $testKeys = ['app_name', 'save', 'cancel', 'nav_dashboard'];
    echo '<table>';
    echo '<tr><th>Clé</th><th>Traduction</th></tr>';
    
    foreach ($testKeys as $key) {
        echo '<tr>';
        echo '<td>' . safe_html($key) . '</td>';
        echo '<td>' . safe_html(app\Helpers\TranslationHelper::translate($key)) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="warning">Impossible de tester le système de traduction: TranslationHelper non disponible</div>';
}

// 5. Interface de changement de langue
echo '<h2>5. Test de changement de langue</h2>';

$currentLang = $_SESSION['user_language'] ?? 'fr';
$availableLangs = [
    'fr' => 'Français',
    'en' => 'English',
    'ar' => 'العربية'
];

echo '<div class="language-switcher">';
echo '<p>Langue actuelle: <strong>' . safe_html($availableLangs[$currentLang] ?? $currentLang) . '</strong></p>';
echo '<p>Changer de langue:</p>';

foreach ($availableLangs as $code => $name) {
    $activeClass = ($code === $currentLang) ? ' class="active"' : '';
    echo '<a href="?set_lang=' . safe_html($code) . '"' . $activeClass . '>' . safe_html($name) . '</a> ';
}

echo '</div>';

// 6. Diagnostic multilangue
echo '<h2>6. Test d\'affichage multilingue</h2>';

echo '<div class="box">';
echo '<h3>Texte en plusieurs langues</h3>';

echo '<table>';
echo '<tr><th>Langue</th><th>Bonjour</th><th>Merci</th><th>Au revoir</th></tr>';

echo '<tr><td>Français</td><td>Bonjour</td><td>Merci</td><td>Au revoir</td></tr>';
echo '<tr><td>English</td><td>Hello</td><td>Thank you</td><td>Goodbye</td></tr>';
echo '<tr><td>العربية</td><td>مرحبا</td><td>شكرا</td><td>وداعا</td></tr>';

echo '</table>';
echo '</div>';

// 7. Actions correctives
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<h2>7. Actions correctives</h2>';
    
    // Créer le dossier de langue
    if (isset($_POST['create_lang_dir'])) {
        if (!is_dir($langDir)) {
            if (mkdir($langDir, 0755, true)) {
                echo '<div class="success">Dossier de langue créé avec succès</div>';
            } else {
                echo '<div class="error">Impossible de créer le dossier de langue</div>';
            }
        }
    }
    
    // Créer les fichiers de langue manquants
    if (isset($_POST['create_lang']) && is_array($_POST['create_lang'])) {
        foreach ($_POST['create_lang'] as $lang) {
            $langFile = $langDir . '/' . $lang;
            
            // Contenu de base pour chaque langue
            $content = '';
            switch ($lang) {
                case 'fr.php':
                    $content = <<<'EOT'
<?php
/**
 * Fichier de langue française (par défaut)
 */
return [
    // Navigation
    'nav_dashboard' => 'Tableau de bord',
    'nav_agents' => 'Agents',
    'nav_responsables' => 'Responsables',
    'nav_services' => 'Services',
    'nav_settings' => 'Paramètres',
    'nav_profile' => 'Mon profil',
    'nav_logout' => 'Déconnexion',
    
    // Commun
    'app_name' => 'AgentExtra',
    'save' => 'Enregistrer',
    'cancel' => 'Annuler',
    'edit' => 'Modifier',
    'delete' => 'Supprimer',
    'search' => 'Rechercher',
    'filter' => 'Filtrer',
    'yes' => 'Oui',
    'no' => 'Non',
    'back' => 'Retour',
];
EOT;
                    break;
                    
                case 'en.php':
                    $content = <<<'EOT'
<?php
/**
 * Fichier de langue anglaise
 */
return [
    // Navigation
    'nav_dashboard' => 'Dashboard',
    'nav_agents' => 'Agents',
    'nav_responsables' => 'Managers',
    'nav_services' => 'Services',
    'nav_settings' => 'Settings',
    'nav_profile' => 'My Profile',
    'nav_logout' => 'Logout',
    
    // Commun
    'app_name' => 'AgentExtra',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'search' => 'Search',
    'filter' => 'Filter',
    'yes' => 'Yes',
    'no' => 'No',
    'back' => 'Back',
];
EOT;
                    break;
                    
                case 'ar.php':
                    $content = <<<'EOT'
<?php
/**
 * Fichier de langue arabe
 */
return [
    // Navigation
    'nav_dashboard' => 'لوحة المعلومات',
    'nav_agents' => 'العملاء',
    'nav_responsables' => 'المسؤولين',
    'nav_services' => 'الخدمات',
    'nav_settings' => 'الإعدادات',
    'nav_profile' => 'ملفي الشخصي',
    'nav_logout' => 'تسجيل الخروج',
    
    // Commun
    'app_name' => 'AgentExtra',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'search' => 'بحث',
    'filter' => 'تصفية',
    'yes' => 'نعم',
    'no' => 'لا',
    'back' => 'رجوع',
];
EOT;
                    break;
            }
            
            if (!empty($content)) {
                if (file_put_contents($langFile, $content)) {
                    echo '<div class="success">Fichier de langue ' . safe_html($lang) . ' créé avec succès</div>';
                } else {
                    echo '<div class="error">Impossible de créer le fichier de langue ' . safe_html($lang) . '</div>';
                }
            }
        }
        
        echo '<script>setTimeout(function() { window.location.reload(); }, 2000);</script>';
    }
}

// Instructions finales
echo <<<HTML
<div class="box">
<h3>Résumé et actions recommandées</h3>
<p>Ce diagnostic vous aide à identifier et résoudre les problèmes liés au système de langues.</p>
<ul>
<li>Assurez-vous que le dossier <code>app/lang</code> existe et contient les fichiers de langue nécessaires.</li>
<li>Vérifiez que les cookies sont activés dans votre navigateur pour la persistance des sessions.</li>
<li>Si vous utilisez HTTPS localement, désactivez <code>session.cookie_secure</code> dans bootstrap.php.</li>
<li>Testez le changement de langue en utilisant les liens ci-dessus.</li>
<li>Vérifiez que TranslationHelper fonctionne correctement.</li>
</ul>
</div>

</body>
</html>
HTML;

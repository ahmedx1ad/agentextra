<?php
/**
 * Script de réparation du système de langues d'AgentExtra
 * 
 * Ce script corrige les problèmes potentiels de changement de langue en:
 * 1. Vérifiant l'intégrité des fichiers de langue
 * 2. Modifiant les paramètres de session si nécessaire
 * 3. Réparant la configuration du système
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer la session
session_start();

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Réparation du système de langues</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #2c3e50; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        button, .button { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        button:hover, .button:hover { background: #2980b9; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Réparation du système de langues d'AgentExtra</h1>
";

// Fonction pour afficher les statuts
function status($message, $type = 'info') {
    $icon = $type === 'success' ? '✓' : ($type === 'error' ? '✗' : 'ℹ');
    echo "<p class='{$type}'>{$icon} {$message}</p>";
}

// Vérifier le fichier bootstrap.php
echo "<h2>1. Vérification des fichiers essentiels</h2>";
if (file_exists(__DIR__ . '/bootstrap.php')) {
    status("Le fichier bootstrap.php existe", 'success');
    require_once __DIR__ . '/bootstrap.php';
} else {
    status("Le fichier bootstrap.php est manquant!", 'error');
    echo "<p>Ce fichier est essentiel pour le fonctionnement de l'application.</p>";
    exit;
}

// Vérifier le helper de traduction
if (file_exists(__DIR__ . '/app/helpers/TranslationHelper.php')) {
    status("Le helper de traduction existe", 'success');
} else {
    status("Le helper de traduction est manquant!", 'error');
    echo "<p>Ce fichier est nécessaire pour les traductions.</p>";
    exit;
}

// Vérifier et créer les fichiers de langue
echo "<h2>2. Vérification des fichiers de langue</h2>";
$langDir = __DIR__ . '/app/lang';

// Vérifier si le dossier des langues existe
if (!is_dir($langDir)) {
    status("Le dossier des langues n'existe pas. Création en cours...", 'warning');
    if (mkdir($langDir, 0755, true)) {
        status("Dossier des langues créé avec succès", 'success');
    } else {
        status("Échec de la création du dossier des langues", 'error');
    }
} else {
    status("Le dossier des langues existe", 'success');
}

// Vérifier les fichiers de langue
$langFiles = [
    'fr.php' => 'Français',
    'en.php' => 'English',
    'ar.php' => 'العربية'
];

echo "<table>
<tr>
    <th>Fichier</th>
    <th>Langue</th>
    <th>Statut</th>
    <th>Action</th>
</tr>";

foreach ($langFiles as $file => $languageName) {
    $filePath = $langDir . '/' . $file;
    echo "<tr>";
    echo "<td>{$file}</td>";
    echo "<td>{$languageName}</td>";
    
    if (file_exists($filePath)) {
        echo "<td class='success'>Présent</td>";
        echo "<td>Aucune action requise</td>";
    } else {
        echo "<td class='error'>Manquant</td>";
        echo "<td><a href='?action=create_lang_file&file={$file}' class='button'>Créer</a></td>";
    }
    
    echo "</tr>";
}

echo "</table>";

// Corriger les paramètres de session
echo "<h2>3. Paramètres de session</h2>";

echo "<div class='box'>";
echo "<h3>Session actuelle</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

// Afficher la langue actuelle
$currentLang = $_SESSION['user_language'] ?? 'fr';
echo "<p>Langue actuelle en session: <strong>{$currentLang}</strong></p>";

echo "<form method='post' action='?action=change_lang'>
    <label for='lang'>Changer la langue de session:</label>
    <select name='lang' id='lang'>
        <option value='fr'" . ($currentLang == 'fr' ? " selected" : "") . ">Français</option>
        <option value='en'" . ($currentLang == 'en' ? " selected" : "") . ">English</option>
        <option value='ar'" . ($currentLang == 'ar' ? " selected" : "") . ">العربية</option>
    </select>
    <button type='submit'>Appliquer</button>
</form>";
echo "</div>";

// Vérification et correction des cookies
echo "<h2>4. Vérification des cookies</h2>";

echo "<div class='box'>";
$sessionName = session_name();
if (isset($_COOKIE[$sessionName])) {
    status("Cookie de session présent ({$sessionName})", 'success');
} else {
    status("Cookie de session absent", 'error');
    echo "<p>Cela peut expliquer pourquoi les sessions ne sont pas conservées.</p>";
}

// Afficher les informations de configuration de PHP relatives aux sessions
echo "<h3>Configuration PHP des sessions</h3>";
echo "<table>
<tr><th>Paramètre</th><th>Valeur</th></tr>
<tr><td>session.save_path</td><td>" . session_save_path() . "</td></tr>
<tr><td>session.cookie_path</td><td>" . ini_get('session.cookie_path') . "</td></tr>
<tr><td>session.cookie_domain</td><td>" . ini_get('session.cookie_domain') . "</td></tr>
<tr><td>session.cookie_secure</td><td>" . ini_get('session.cookie_secure') . "</td></tr>
<tr><td>session.cookie_httponly</td><td>" . ini_get('session.cookie_httponly') . "</td></tr>
<tr><td>session.cookie_samesite</td><td>" . ini_get('session.cookie_samesite') . "</td></tr>
</table>";
echo "</div>";

// Test de fonctionnalité
echo "<h2>5. Test de fonctionnalité</h2>";

echo "<div class='box'>";
echo "<h3>Test d'affichage multilingue</h3>";
echo "<div style='display: flex; gap: 20px; flex-wrap: wrap;'>";

echo "<div>
    <h4>Français</h4>
    <p>Bienvenue sur AgentExtra</p>
    <p>Ceci est un test d'affichage</p>
</div>";

echo "<div>
    <h4>English</h4>
    <p>Welcome to AgentExtra</p>
    <p>This is a display test</p>
</div>";

echo "<div dir='rtl' style='text-align: right;'>
    <h4>العربية</h4>
    <p>مرحبًا بكم في AgentExtra</p>
    <p>هذا اختبار عرض</p>
</div>";

echo "</div>";
echo "</div>";

// Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'create_lang_file':
            $file = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING);
            $filePath = $langDir . '/' . $file;
            
            if (!in_array($file, array_keys($langFiles))) {
                status("Fichier non autorisé", 'error');
                break;
            }
            
            // Contenu par défaut selon la langue
            switch ($file) {
                case 'ar.php':
                    $content = <<<EOT
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
                case 'en.php':
                    $content = <<<EOT
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
                case 'fr.php':
                default:
                    $content = <<<EOT
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
            }
            
            if (file_put_contents($filePath, $content)) {
                status("Fichier {$file} créé avec succès", 'success');
            } else {
                status("Échec de la création du fichier {$file}", 'error');
            }
            
            // Rediriger vers la page principale après 2 secondes
            echo "<script>setTimeout(function() { window.location.href = 'fix_language_settings.php'; }, 2000);</script>";
            break;
            
        case 'change_lang':
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lang'])) {
                $newLang = filter_input(INPUT_POST, 'lang', FILTER_SANITIZE_STRING);
                if (in_array($newLang, ['fr', 'en', 'ar'])) {
                    $_SESSION['user_language'] = $newLang;
                    status("Langue changée en: {$newLang}", 'success');
                } else {
                    status("Langue non valide", 'error');
                }
                
                // Rediriger vers la page principale après 1 seconde
                echo "<script>setTimeout(function() { window.location.href = 'fix_language_settings.php'; }, 1000);</script>";
            }
            break;
    }
}

// Liens de navigation
echo "<h2>6. Actions disponibles</h2>";
echo "<div class='box'>";
echo "<p><a href='settings' class='button'>Accéder aux paramètres de l'application</a></p>";
echo "<p><a href='javascript:void(0);' onclick='resetSessionAndRedirect()' class='button'>Réinitialiser la session et rediriger vers les paramètres</a></p>";
echo "<p><a href='settings/language/ar' class='button'>Forcer le changement en arabe</a></p>";
echo "</div>";

echo "<script>
function resetSessionAndRedirect() {
    // Créer un élément iframe caché
    var iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '?action=change_lang';
    document.body.appendChild(iframe);
    
    // Rediriger après un court délai
    setTimeout(function() {
        window.location.href = 'settings';
    }, 1000);
}
</script>";

echo "</body></html>"; 
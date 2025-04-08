<?php
// Chargement de la configuration en premier
$config = require_once __DIR__ . '/config/config.php';

// Définition des constantes
define('APP_NAME', $config['app']['name']);
define('APP_VERSION', $config['app']['version']);
define('APP_ENV', $config['app']['env']);
define('APP_URL', $config['app']['url']);
define('ROOT_PATH', $config['paths']['root']);
define('APP_PATH', $config['paths']['app']);
define('CONFIG_PATH', $config['paths']['config']);
define('UPLOADS_PATH', $config['paths']['uploads']);
define('VIEWS_PATH', $config['paths']['views']);
define('EXPORTS_PATH', __DIR__ . '/public/exports');

// Configuration de la session
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', $config['security']['session_lifetime']);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.name', $config['security']['session_name']);

// Démarrage de la session
session_start();

// Régénération périodique de l'ID de session
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Initialiser la langue de l'utilisateur (par défaut: français)
$currentLang = $_SESSION['user_language'] ?? 'fr';

// Créer le dossier de langues s'il n'existe pas
if (!is_dir(__DIR__ . '/app/lang')) {
    mkdir(__DIR__ . '/app/lang', 0755, true);
}

// Initialiser le helper de traduction
require_once __DIR__ . '/app/helpers/TranslationHelper.php';
app\Helpers\TranslationHelper::init($currentLang);

// Protection contre le CSRF
if (!isset($_SESSION['csrf_token']) || 
    !isset($_SESSION['csrf_token_time']) || 
    time() - $_SESSION['csrf_token_time'] > $config['security']['csrf_token_lifetime']) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

// Configuration de PHP
ini_set('display_errors', APP_ENV === 'development' ? 1 : 0);
error_reporting(APP_ENV === 'development' ? E_ALL : 0);
date_default_timezone_set($config['app']['timezone']);
mb_internal_encoding($config['app']['charset']);

// Autoloader pour les classes
spl_autoload_register(function ($class) {
    // Convertir le namespace en chemin de fichier
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    
    // Construire le chemin complet
    $file = __DIR__ . DIRECTORY_SEPARATOR . $file . '.php';
    
    // Débogage en mode développement
    if (APP_ENV === 'development') {
        error_log("Tentative de chargement de la classe : $class");
        error_log("Chemin du fichier : $file");
        error_log("Le fichier existe : " . (file_exists($file) ? 'Oui' : 'Non'));
    }
    
    // Charger le fichier s'il existe
    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});

// Fonction d'aide pour le débogage
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

// Fonction pour obtenir la configuration
function config($key = null) {
    global $config;
    if ($key === null) {
        return $config;
    }
    $keys = explode('.', $key);
    $value = $config;
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return null;
        }
        $value = $value[$k];
    }
    return $value;
}

// Fonction pour obtenir l'URL de base
function base_url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

// Fonction pour rediriger
function redirect($path) {
    // S'assurer que les données de session sont écrites avant la redirection
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    
    header('Location: ' . base_url($path));
    exit;
}

// Fonction pour échapper les caractères HTML
function e($string) {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fonction pour générer un jeton CSRF
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier le jeton CSRF
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour obtenir les messages flash
function get_flash_message($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

// Fonction pour définir un message flash
function set_flash_message($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

// Fonction pour déboguer les chemins
function debug_paths() {
    error_log("Debug des chemins :");
    error_log("ROOT_PATH : " . ROOT_PATH);
    error_log("APP_PATH : " . APP_PATH);
    error_log("CONFIG_PATH : " . CONFIG_PATH);
    error_log("VIEWS_PATH : " . VIEWS_PATH);
    error_log("__DIR__ : " . __DIR__);
    error_log("getcwd() : " . getcwd());
}

// Appeler le débogage des chemins au démarrage en mode développement
if (APP_ENV === 'development') {
    debug_paths();
} 
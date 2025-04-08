<?php
// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Réinitialisation des paramètres</h1>";

try {
    // Get database connection
    $db = app\Config\DB::getInstance();
    
    // First, truncate the settings table to start fresh
    echo "<p>Suppression des paramètres existants...</p>";
    $db->exec("TRUNCATE TABLE settings");
    
    // Create a new SettingsController which will initialize settings
    echo "<p>Création de nouveaux paramètres par défaut...</p>";
    $controller = new app\Controllers\SettingsController();
    
    // Count settings to verify
    $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Nombre de paramètres créés: " . $result['count'] . "</p>";
    
    echo "<p style='color: green; font-weight: bold;'>Paramètres réinitialisés avec succès!</p>";
    echo "<p><a href='settings'>Retour à la page des paramètres</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>Erreur: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez les logs PHP pour plus de détails.</p>";
} 
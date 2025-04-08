<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Correction forcée des paramètres</h1>";

try {
    // Obtenir la connexion à la base de données
    $db = app\Config\DB::getInstance();
    
    // 1. Recréer la table settings
    echo "<p>1. Recréation de la table settings...</p>";
    $db->exec("DROP TABLE IF EXISTS settings");
    
    $sql = "CREATE TABLE settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        value TEXT,
        category VARCHAR(50) DEFAULT 'general',
        description TEXT,
        type VARCHAR(20) DEFAULT 'text',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "<p style='color:green'>✓ Table settings recréée avec succès</p>";
    
    // 2. Insérer directement les paramètres par défaut
    echo "<p>2. Insertion des paramètres par défaut...</p>";
    
    $defaultSettings = [
        // Informations générales
        ['app_name', 'AgentExtra', 'general', 'Nom de l\'application', 'text'],
        ['company_name', '', 'general', 'Nom de l\'entreprise', 'text'],
        ['logo_path', '', 'appearance', 'Logo de l\'application', 'file'],
        
        // Contact
        ['email_contact', '', 'contact', 'Email de contact principal', 'email'],
        ['phone_contact', '', 'contact', 'Téléphone de contact', 'text'],
        ['address', '', 'contact', 'Adresse de l\'entreprise', 'textarea'],
        
        // Localisation
        ['language', 'fr', 'localization', 'Langue par défaut', 'select'],
        ['timezone', 'Africa/Casablanca', 'localization', 'Fuseau horaire', 'select'],
        ['date_format', 'd/m/Y', 'localization', 'Format de date', 'select'],
        ['time_format', 'H:i', 'localization', 'Format d\'heure', 'select'],
        
        // Système
        ['maintenance_mode', '0', 'system', 'Activer le mode maintenance', 'checkbox'],
        ['debug_mode', '0', 'system', 'Activer le mode debug', 'checkbox'],
        ['enable_cache', '1', 'system', 'Activer le cache', 'checkbox'],
        ['cache_duration', '3600', 'system', 'Durée du cache (secondes)', 'number'],
        
        // Sécurité
        ['password_min_length', '8', 'security', 'Longueur minimale du mot de passe', 'number'],
        ['password_require_special', '1', 'security', 'Exiger des caractères spéciaux', 'checkbox'],
        ['password_require_uppercase', '1', 'security', 'Exiger des majuscules', 'checkbox'],
        ['password_require_numbers', '1', 'security', 'Exiger des chiffres', 'checkbox'],
        ['session_timeout', '30', 'security', 'Délai d\'expiration de session (minutes)', 'number'],
        
        // Email
        ['smtp_host', '', 'email', 'Hôte SMTP', 'text'],
        ['smtp_port', '587', 'email', 'Port SMTP', 'number'],
        ['smtp_username', '', 'email', 'Nom d\'utilisateur SMTP', 'text'],
        ['smtp_password', '', 'email', 'Mot de passe SMTP', 'password'],
        ['smtp_encryption', 'tls', 'email', 'Chiffrement SMTP', 'select'],
        ['email_from_name', 'AgentExtra', 'email', 'Nom d\'expéditeur', 'text'],
        
        // Notifications
        ['enable_email_notifications', '1', 'notifications', 'Activer les notifications par email', 'checkbox'],
        ['notify_on_new_agent', '1', 'notifications', 'Notifier lors de l\'ajout d\'un agent', 'checkbox'],
        ['notify_on_agent_update', '0', 'notifications', 'Notifier lors de la modification d\'un agent', 'checkbox'],
        
        // Export
        ['export_page_size', 'A4', 'export', 'Format de page pour les exports PDF', 'select'],
        ['export_orientation', 'portrait', 'export', 'Orientation pour les exports PDF', 'select'],
        ['export_include_header', '1', 'export', 'Inclure l\'en-tête dans les exports', 'checkbox'],
        ['export_include_footer', '1', 'export', 'Inclure le pied de page dans les exports', 'checkbox']
    ];
    
    $insertCount = 0;
    foreach ($defaultSettings as $setting) {
        $stmt = $db->prepare("INSERT INTO settings (name, value, category, description, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($setting);
        $insertCount++;
    }
    
    echo "<p style='color:green'>✓ " . $insertCount . " paramètres insérés avec succès</p>";
    
    // Vérifier le résultat
    $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Nombre total de paramètres dans la base de données: " . $result['count'] . "</p>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-radius: 5px;'>";
    echo "<h3>Que faire maintenant?</h3>";
    echo "<p>Les paramètres ont été correctement insérés dans la base de données. Vous pouvez maintenant:</p>";
    echo "<p><a href='settings' class='button' style='background-color: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Aller à la page des paramètres</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>ERREUR: " . $e->getMessage() . "</p>";
    echo "<p>Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
} 
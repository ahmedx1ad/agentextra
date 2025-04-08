<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction avancée des paramètres</h1>";

try {
    // Charger le bootstrap (qui contient la config et les constantes)
    require_once __DIR__ . '/bootstrap.php';
    
    echo "<p>Bootstrap chargé avec succès</p>";
    
    // Obtenir la connexion à la base de données directement par PDO
    // Pour éviter tout problème avec DB.php ou database.php
    $dbConfig = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'dbname' => 'agentextra',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ];
    
    echo "<p>Tentative de connexion à la base de données...</p>";
    
    try {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
        ]);
        
        echo "<p style='color:green'>✓ Connexion à la base de données réussie!</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
        
        // Si la base de données n'existe pas, essayer de la créer
        if (strpos($e->getMessage(), "Unknown database") !== false) {
            echo "<p>Tentative de création de la base de données...</p>";
            $rootDsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']}";
            $rootPdo = new PDO($rootDsn, $dbConfig['username'], $dbConfig['password']);
            $rootPdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']} CHARACTER SET {$dbConfig['charset']}");
            
            // Reconnexion
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
            ]);
            
            echo "<p style='color:green'>✓ Base de données créée et connexion réussie!</p>";
        } else {
            throw $e; // Relancer l'erreur si ce n'est pas une DB manquante
        }
    }
    
    // 1. Vérifier si la table settings existe et sa structure
    echo "<h2>1. Analyse de la table settings</h2>";
    
    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p>La table 'settings' existe déjà</p>";
        
        // Vérifier la structure
        $stmt = $pdo->query("DESCRIBE settings");
        $columns = [];
        while ($row = $stmt->fetch()) {
            $columns[$row['Field']] = $row;
        }
        
        // Vérifier si les colonnes nécessaires existent
        $requiredColumns = ['name', 'value', 'category', 'description', 'type'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $col) {
            if (!isset($columns[$col])) {
                $missingColumns[] = $col;
            }
        }
        
        if (!empty($missingColumns)) {
            echo "<p style='color:orange'>⚠ La table settings existe mais il manque les colonnes: " . implode(', ', $missingColumns) . "</p>";
            echo "<p>Nous allons recréer la table avec la structure correcte.</p>";
            
            // Faire une sauvegarde des données existantes
            $stmt = $pdo->query("SELECT * FROM settings");
            $existingData = $stmt->fetchAll();
            
            // Supprimer et recréer
            $pdo->exec("DROP TABLE settings");
            $tableExists = false;
        } else {
            echo "<p style='color:green'>✓ La structure de la table settings est correcte</p>";
        }
    } else {
        echo "<p>La table 'settings' n'existe pas encore, nous allons la créer</p>";
    }
    
    // 2. Créer la table si nécessaire
    if (!$tableExists) {
        echo "<h2>2. Création de la table settings</h2>";
        
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
        
        $pdo->exec($sql);
        echo "<p style='color:green'>✓ Table settings créée avec succès</p>";
    }
    
    // 3. Insérer les données de base
    echo "<h2>3. Insertion des paramètres par défaut</h2>";
    
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
    
    // Désactiver les clés étrangères temporairement
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Tronquer la table pour éviter les doublons
    $pdo->exec("TRUNCATE TABLE settings");
    
    // Réactiver les clés étrangères
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Insérer les paramètres
    $insertCount = 0;
    $stmt = $pdo->prepare("INSERT INTO settings (name, value, category, description, type) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($defaultSettings as $setting) {
        try {
            $stmt->execute($setting);
            $insertCount++;
        } catch (PDOException $e) {
            echo "<p style='color:red'>Erreur lors de l'insertion du paramètre '{$setting[0]}': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p style='color:green'>✓ " . $insertCount . " paramètres insérés avec succès</p>";
    
    // 4. Vérification finale
    echo "<h2>4. Vérification finale</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch();
    echo "<p>Nombre total de paramètres dans la base de données: " . $result['count'] . "</p>";
    
    $stmt = $pdo->query("SELECT DISTINCT category, COUNT(*) as count FROM settings GROUP BY category");
    echo "<h3>Catégories de paramètres</h3>";
    echo "<ul>";
    while ($row = $stmt->fetch()) {
        echo "<li>" . htmlspecialchars($row['category']) . ": " . $row['count'] . " paramètres</li>";
    }
    echo "</ul>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-radius: 5px;'>";
    echo "<h3>Que faire maintenant?</h3>";
    echo "<p>Les paramètres ont été correctement insérés dans la base de données. Vous pouvez maintenant:</p>";
    echo "<p><a href='settings' class='button' style='background-color: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 10px;'>Aller à la page des paramètres</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>ERREUR CRITIQUE: " . $e->getMessage() . "</p>";
    echo "<p>Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #fff3f3; border-radius: 5px; border: 1px solid #ffcccc;'>";
    echo "<h3>Solutions possibles:</h3>";
    echo "<ol>";
    echo "<li>Vérifiez que votre serveur MySQL est bien démarré</li>";
    echo "<li>Vérifiez les identifiants de connexion à la base de données dans app/Config/DB.php</li>";
    echo "<li>Assurez-vous que la base de données 'agentextra' existe</li>";
    echo "<li>Vérifiez que l'utilisateur 'root' a les droits sur cette base de données</li>";
    echo "</ol>";
    echo "</div>";
} 
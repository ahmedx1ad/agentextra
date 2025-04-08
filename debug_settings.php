<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Diagnostic des paramètres</h1>";

try {
    // 1. Vérifier la connexion à la base de données
    echo "<h2>1. Connexion à la base de données</h2>";
    $db = app\Config\DB::getInstance();
    echo "<p style='color:green'>✓ Connexion à la base de données réussie</p>";
    
    // 2. Vérifier si la table settings existe
    echo "<h2>2. Vérification de la table settings</h2>";
    $stmt = $db->query("SHOW TABLES LIKE 'settings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color:green'>✓ La table 'settings' existe</p>";
        
        // 2.1 Vérifier la structure de la table
        echo "<h3>2.1 Structure de la table settings</h3>";
        $stmt = $db->query("DESCRIBE settings");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        
        // 2.2 Compter les paramètres
        $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
        
        if ($count > 0) {
            echo "<p style='color:green'>✓ Il y a " . $count . " paramètres dans la table</p>";
            
            // 2.3 Afficher les catégories de paramètres
            $stmt = $db->query("SELECT DISTINCT category, COUNT(*) as count FROM settings GROUP BY category");
            echo "<h3>2.3 Catégories de paramètres</h3>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Catégorie</th><th>Nombre de paramètres</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr><td>" . htmlspecialchars($row['category']) . "</td><td>" . $row['count'] . "</td></tr>";
            }
            echo "</table>";
            
            // 2.4 Échantillon de paramètres
            echo "<h3>2.4 Échantillon de paramètres</h3>";
            $stmt = $db->query("SELECT * FROM settings LIMIT 5");
            echo "<table border='1' cellpadding='5' style='word-break: break-all;'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Valeur</th><th>Catégorie</th><th>Description</th><th>Type</th></tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['value']) . "</td>";
                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color:red'>✗ La table 'settings' est vide!</p>";
        }
    } else {
        echo "<p style='color:red'>✗ La table 'settings' n'existe pas!</p>";
        
        // Créer la table settings
        echo "<h3>Création de la table settings</h3>";
        $sql = "CREATE TABLE IF NOT EXISTS settings (
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
        echo "<p>Table 'settings' créée</p>";
    }
    
    // 3. Tester la classe SettingsController
    echo "<h2>3. Test de la classe SettingsController</h2>";
    
    // Vérifier si la classe existe
    if (class_exists('app\Controllers\SettingsController')) {
        echo "<p style='color:green'>✓ La classe SettingsController existe</p>";
        
        // Créer une instance sans erreur
        echo "<p>Création d'une nouvelle instance de SettingsController...</p>";
        $controller = new app\Controllers\SettingsController();
        echo "<p style='color:green'>✓ Instance de SettingsController créée avec succès</p>";
        
        // Vérifier si l'initialisation a fonctionné
        $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $newCount = $result['count'];
        
        if ($newCount > 0 && (!isset($count) || $newCount > $count)) {
            echo "<p style='color:green'>✓ L'initialisation des paramètres a fonctionné! Nombre de paramètres: " . $newCount . "</p>";
        } else {
            echo "<p style='color:orange'>⚠ L'initialisation des paramètres n'a pas ajouté de nouveaux paramètres.</p>";
        }
    } else {
        echo "<p style='color:red'>✗ La classe SettingsController n'existe pas!</p>";
    }
    
    // 4. Tester la vue des paramètres
    echo "<h2>4. Test de la vue des paramètres</h2>";
    $viewPath = VIEWS_PATH . '/settings/index.php';
    
    if (file_exists($viewPath)) {
        echo "<p style='color:green'>✓ Le fichier de vue existe: " . $viewPath . "</p>";
        
        // Afficher les chemins importants
        echo "<h3>Chemins importants</h3>";
        echo "<p>ROOT_PATH: " . ROOT_PATH . "</p>";
        echo "<p>VIEWS_PATH: " . VIEWS_PATH . "</p>";
        echo "<p>APP_PATH: " . APP_PATH . "</p>";
    } else {
        echo "<p style='color:red'>✗ Le fichier de vue n'existe pas: " . $viewPath . "</p>";
    }
    
    // 5. Actions à faire
    echo "<h2>5. Actions à faire</h2>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    
    if (!$tableExists || (isset($count) && $count == 0)) {
        echo "<p><a href='reset_settings.php' class='button' style='background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Réinitialiser les paramètres</a></p>";
    }
    
    echo "<p><a href='settings' class='button' style='background-color: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; margin-top: 10px;'>Aller à la page des paramètres</a></p>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color:red; font-weight:bold;'>ERREUR: " . $e->getMessage() . "</p>";
    echo "<p>Trace: <pre>" . $e->getTraceAsString() . "</pre></p>";
} 
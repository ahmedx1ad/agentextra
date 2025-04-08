<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

use App\Config\DB;

try {
    echo "Début de la vérification de la base de données...\n";
    
    $db = DB::getInstance();
    
    // Vérifier si la table users existe
    $stmt = $db->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        echo "La table users n'existe pas. Création...\n";
        
        // Créer la table users
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') DEFAULT 'user',
            first_name VARCHAR(50),
            last_name VARCHAR(50),
            phone VARCHAR(20),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        
        echo "Table users créée avec succès.\n";
        
        // Créer un utilisateur admin par défaut
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (email, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $db->prepare($sql)->execute(['admin@example.com', $password, 'admin', 'Administrateur', '', 1]);
        
        echo "Utilisateur admin créé avec succès.\n";
    } else {
        echo "La table users existe déjà.\n";
        
        // Vérifier la structure de la table users
        $stmt = $db->query("DESCRIBE users");
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'];
        }
        
        echo "Colonnes existantes : " . implode(', ', $columns) . "\n";
        
        // Vérifier si la colonne phone existe
        if (!in_array('phone', $columns)) {
            echo "La colonne phone n'existe pas. Ajout...\n";
            $db->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER last_name");
            echo "Colonne phone ajoutée avec succès.\n";
        }
        
        // Vérifier si la colonne updated_at existe
        if (!in_array('updated_at', $columns)) {
            echo "La colonne updated_at n'existe pas. Ajout...\n";
            $db->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "Colonne updated_at ajoutée avec succès.\n";
        }
        
        // Vérifier si un utilisateur admin existe
        $stmt = $db->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
        if ($stmt->rowCount() === 0) {
            echo "Aucun utilisateur admin trouvé. Création...\n";
            
            // Créer un utilisateur admin par défaut
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?)";
            $db->prepare($sql)->execute(['admin@example.com', $password, 'admin', 'Administrateur', '', 1]);
            
            echo "Utilisateur admin créé avec succès.\n";
        } else {
            echo "Un utilisateur admin existe déjà.\n";
            
            // Vérifier si le mot de passe de l'admin est correct
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!password_verify('admin123', $admin['password'])) {
                echo "Mise à jour du mot de passe admin...\n";
                $password = password_hash('admin123', PASSWORD_DEFAULT);
                $db->exec("UPDATE users SET password = '$password' WHERE id = {$admin['id']}");
                echo "Mot de passe admin mis à jour avec succès.\n";
            }
        }
    }
    
    // Vérifier si la table settings existe
    $stmt = $db->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() === 0) {
        echo "La table settings n'existe pas. Création...\n";
        
        // Créer la table settings
        $sql = "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
        
        echo "Table settings créée avec succès.\n";
        
        // Paramètres par défaut
        $defaultSettings = [
            'app_name' => 'AgentExtra',
            'company_name' => '',
            'email_contact' => '',
            'phone_contact' => '',
            'language' => 'fr',
            'timezone' => 'Africa/Casablanca'
        ];
        
        // Insérer les paramètres par défaut
        foreach ($defaultSettings as $name => $value) {
            $stmt = $db->prepare("INSERT INTO settings (name, value) VALUES (?, ?)");
            $stmt->execute([$name, $value]);
        }
        
        echo "Paramètres par défaut insérés avec succès.\n";
    } else {
        echo "La table settings existe déjà.\n";
    }
    
    echo "Vérification de la base de données terminée avec succès.\n";
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
} 
<?php
// Script pour diagnostiquer et corriger les problèmes de base de données

// Afficher toutes les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'agentextra';
$username = 'root';
$password = '';

echo "====== Diagnostic et correction de la base de données ======\n\n";

try {
    // Connexion directe à la base de données
    echo "Connexion à la base de données...\n";
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connexion réussie.\n\n";
    
    // Vérifier si la base de données existe
    echo "Vérification de l'existence de la base de données '$dbname'...\n";
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = $stmt->fetchColumn();
    
    if (!$dbExists) {
        echo "La base de données '$dbname' n'existe pas. Création en cours...\n";
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Base de données créée avec succès.\n";
    } else {
        echo "La base de données existe déjà.\n";
    }
    
    // Se connecter à la base de données
    $pdo->exec("USE `$dbname`");
    
    // Vérifier l'existence des tables principales
    $requiredTables = ['agents', 'services', 'responsables', 'users', 'activity_logs'];
    echo "\nVérification des tables requises:\n";
    
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $missingTables = array_diff($requiredTables, $existingTables);
    
    if (empty($missingTables)) {
        echo "Toutes les tables requises existent.\n";
    } else {
        echo "Tables manquantes: " . implode(', ', $missingTables) . "\n";
        echo "Création des tables manquantes...\n";
        
        // Créer les tables manquantes
        foreach ($missingTables as $table) {
            switch ($table) {
                case 'activity_logs':
                    $pdo->exec("
                        CREATE TABLE `activity_logs` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `user_id` int(11) DEFAULT NULL,
                            `action` varchar(255) NOT NULL,
                            `description` text DEFAULT NULL,
                            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                            PRIMARY KEY (`id`),
                            KEY `user_id` (`user_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                    ");
                    echo "Table 'activity_logs' créée.\n";
                    break;
                
                // Ajouter d'autres tables selon les besoins
                
                default:
                    echo "Pas de structure définie pour la table '$table'.\n";
            }
        }
    }
    
    // Vérifier les colonnes dans les tables
    echo "\nVérification des colonnes essentielles:\n";
    
    // Vérifier la table agents
    if (in_array('agents', $existingTables)) {
        $columns = $pdo->query("SHOW COLUMNS FROM agents")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('statut', $columns)) {
            echo "Ajout de la colonne 'statut' à la table 'agents'...\n";
            $pdo->exec("ALTER TABLE agents ADD COLUMN statut ENUM('actif', 'inactif') NOT NULL DEFAULT 'actif'");
        }
        
        if (!in_array('responsable_id', $columns)) {
            echo "Ajout de la colonne 'responsable_id' à la table 'agents'...\n";
            $pdo->exec("ALTER TABLE agents ADD COLUMN responsable_id INT(11) NULL DEFAULT NULL");
        }
    }
    
    // Récapitulatif final
    echo "\n====== Récapitulatif ======\n";
    echo "Base de données: $dbname\n";
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables présentes: " . implode(', ', $tables) . "\n\n";
    
    // Vérifier le nombre d'enregistrements dans chaque table
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "Table '$table': $count enregistrements\n";
    }
    
} catch (PDOException $e) {
    echo "ERREUR PDO: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "\n====== Fin du diagnostic ======\n"; 
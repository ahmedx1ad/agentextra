<?php
// Database connection parameters
$host = 'localhost';
$db   = 'agentextra';
$user = 'root';
$pass = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connexion à la base de données réussie.<br>";
    
    // Vérifier si les colonnes existent déjà
    $columnsToCheck = ['email', 'username', 'updated_at'];
    $existingColumns = [];
    
    foreach ($columnsToCheck as $column) {
        $stmt = $pdo->query("SELECT COUNT(*) as column_exists 
                            FROM information_schema.columns 
                            WHERE table_schema = '$db' 
                            AND table_name = 'users' 
                            AND column_name = '$column'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ((int)$row['column_exists'] > 0) {
            $existingColumns[] = $column;
            echo "La colonne '$column' existe déjà dans la table users.<br>";
        }
    }
    
    // SQL pour ajouter les colonnes manquantes
    if (!in_array('email', $existingColumns)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) NULL AFTER `password_hash`");
        echo "Colonne 'email' ajoutée avec succès.<br>";
    }
    
    if (!in_array('username', $existingColumns)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `email`");
        echo "Colonne 'username' ajoutée avec succès.<br>";
    }
    
    if (!in_array('updated_at', $existingColumns)) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "Colonne 'updated_at' ajoutée avec succès.<br>";
    }
    
    // Ajouter des index uniques
    try {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_email` (`email`)");
        echo "Index unique pour 'email' ajouté avec succès.<br>";
    } catch (PDOException $e) {
        // L'index peut déjà exister, donc ignorer cette erreur
        echo "Note: " . $e->getMessage() . "<br>";
    }
    
    try {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_username` (`username`)");
        echo "Index unique pour 'username' ajouté avec succès.<br>";
    } catch (PDOException $e) {
        // L'index peut déjà exister, donc ignorer cette erreur
        echo "Note: " . $e->getMessage() . "<br>";
    }
    
    echo "<strong>Mise à jour de la base de données terminée avec succès.</strong><br>";
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage();
}
?> 
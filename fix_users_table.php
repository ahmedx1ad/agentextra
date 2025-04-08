<?php
// Afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correcteur de la table utilisateurs</h1>";

// Configuration de la base de données
$host = '127.0.0.1';
$dbname = 'agentextra';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la table existe
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (count($tables) === 0) {
        echo "<p style='color:red'>Table 'users' non trouvée!</p>";
        exit;
    }
    
    // Récupérer les colonnes existantes
    $result = $pdo->query("DESCRIBE users");
    $existingColumns = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $existingColumns[$row['Field']] = $row;
    }
    
    echo "<p>Colonnes existantes: " . implode(", ", array_keys($existingColumns)) . "</p>";
    
    // Colonnes requises pour le profil utilisateur
    $requiredColumns = [
        'id' => "INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY",
        'name' => "VARCHAR(255)",
        'email' => "VARCHAR(255) NOT NULL",
        'first_name' => "VARCHAR(100)",
        'last_name' => "VARCHAR(100)",
        'phone' => "VARCHAR(20)",
        'password_hash' => "VARCHAR(255) NOT NULL",
        'role' => "VARCHAR(50) DEFAULT 'user'",
        'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
    ];
    
    // Vérifier et ajouter les colonnes manquantes
    $alterStatements = [];
    foreach ($requiredColumns as $column => $definition) {
        if (!isset($existingColumns[$column])) {
            $alterStatements[] = "ADD COLUMN `$column` $definition";
            echo "<p style='color:orange'>Colonne manquante: $column</p>";
        }
    }
    
    // Exécuter les modifications si nécessaire
    if (!empty($alterStatements)) {
        $alterSQL = "ALTER TABLE users " . implode(", ", $alterStatements);
        echo "<p>Exécution: $alterSQL</p>";
        $pdo->exec($alterSQL);
        echo "<p style='color:green'>Modifications appliquées avec succès!</p>";
    } else {
        echo "<p style='color:green'>Aucune modification nécessaire. La structure de la table est correcte.</p>";
    }
    
    echo "<p><a href='check_db.php'>Vérifier la structure mise à jour</a></p>";
    echo "<p><a href='profile'>Retourner au profil</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Erreur de base de données</h2>";
    echo "<p style='color:red'>Message: " . $e->getMessage() . "</p>";
}
?> 
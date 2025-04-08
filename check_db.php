<?php
// Afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configuration de la base de données
$host = '127.0.0.1';
$dbname = 'agentextra';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Structure de la table utilisateurs</h1>";
    
    // Récupérer la structure de la table users
    $stmt = $pdo->query("DESCRIBE users");
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        foreach ($row as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Vérifier s'il y a des données
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "<p>Nombre d'utilisateurs: " . $count . "</p>";
    
    // Afficher un exemple de données si disponible
    if ($count > 0) {
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        echo "<h2>Exemple de données utilisateur</h2>";
        echo "<pre>";
        print_r($stmt->fetch(PDO::FETCH_ASSOC));
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Erreur de base de données</h1>";
    echo "<p>Message: " . $e->getMessage() . "</p>";
}
?> 
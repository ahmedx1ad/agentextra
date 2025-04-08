<?php
// Afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger le bootstrap
require_once 'bootstrap.php';

echo "<h1>Structure de la table agents</h1>";

try {
    // Connexion à la base de données
    $db = \App\Config\DB::getInstance();
    
    // Obtenir la structure de la table
    $stmt = $db->query("DESCRIBE agents");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Vérification de la colonne 'categorie'</h2>";
    $categorieExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'categorie') {
            $categorieExists = true;
            echo "<p>La colonne 'categorie' existe déjà avec le type: " . $column['Type'] . "</p>";
            break;
        }
    }
    
    if (!$categorieExists) {
        echo "<p>La colonne 'categorie' n'existe pas encore dans la table agents.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
} 
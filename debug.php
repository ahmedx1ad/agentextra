<?php 
// Afficher toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chargement du bootstrap
require_once __DIR__ . '/bootstrap.php';

// Tester la connexion à la base de données
echo "<h2>Test de connexion à la base de données</h2>";
try {
    $db = \app\Config\DB::getInstance();
    echo "<p style='color:green'>✓ Connexion à la base de données réussie</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
    exit;
}

// Tester la récupération du responsable avec ID=1
echo "<h2>Test de récupération du responsable (ID=1)</h2>";
try {
    $query = "SELECT * FROM responsables WHERE id = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $responsable = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($responsable) {
        echo "<p style='color:green'>✓ Responsable trouvé:</p>";
        echo "<pre>";
        var_dump($responsable);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>✗ Aucun responsable trouvé avec ID=1</p>";
        
        // Vérifier s'il y a d'autres responsables
        $query = "SELECT id FROM responsables LIMIT 5";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($ids)) {
            echo "<p>Autres IDs de responsables disponibles: " . implode(", ", $ids) . "</p>";
        } else {
            echo "<p>Aucun responsable n'existe dans la base de données.</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Erreur lors de la requête: " . $e->getMessage() . "</p>";
}

// Tester la structure de la table responsables
echo "<h2>Structure de la table responsables</h2>";
try {
    $query = "DESCRIBE responsables";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
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
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Erreur lors de la récupération de la structure: " . $e->getMessage() . "</p>";
}

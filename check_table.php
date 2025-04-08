<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Script démarré\n";

try {
    echo "Chargement de bootstrap.php...\n";
    require_once 'bootstrap.php';
    echo "Bootstrap chargé avec succès\n";
    
    echo "Tentative de connexion à la base de données...\n";
    $db = \App\Config\DB::getInstance();
    echo "Connexion à la base de données réussie\n";
    
    // Obtenir la structure de la table agents
    echo "Exécution de la requête DESCRIBE agents...\n";
    $result = $db->query("DESCRIBE agents");
    
    echo "Structure de la table agents:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . "\n";
    }
    
    // Vérifier l'existence des colonnes taille et poids
    echo "Vérification des colonnes spécifiques...\n";
    $columns = $db->query("SHOW COLUMNS FROM agents WHERE Field IN ('taille', 'poids')");
    $columnData = $columns->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Résultats de la vérification des colonnes:\n";
    if (empty($columnData)) {
        echo "ATTENTION: Aucune colonne 'taille' ou 'poids' n'a été trouvée dans la table agents\n";
    } else {
        foreach ($columnData as $column) {
            echo "- " . $column['Field'] . " existe dans la table agents\n";
        }
        
        if (count($columnData) < 2) {
            echo "ATTENTION: Une colonne est manquante parmi 'taille' et 'poids' dans la table agents\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erreur d'exécution: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "Erreur PHP: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Fin du script\n"; 
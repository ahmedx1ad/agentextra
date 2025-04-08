<?php
// Inclusion du fichier de configuration de la base de données
require_once 'config/database.php';

try {
    // Obtenir la structure de la table responsables
    $sql = "DESCRIBE responsables";
    $stmt = $GLOBALS['pdo']->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Structure de la table responsables</h2>";
    echo "<pre>";
    print_r($columns);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<h2>Erreur :</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
} 
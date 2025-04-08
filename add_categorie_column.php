<?php
// Afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Charger le bootstrap
require_once 'bootstrap.php';

echo "<h1>Ajout de la colonne 'categorie' à la table agents</h1>";

try {
    // Connexion à la base de données
    $db = \App\Config\DB::getInstance();
    
    // Vérifier si la colonne existe déjà
    $stmt = $db->query("SHOW COLUMNS FROM agents LIKE 'categorie'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "<p>La colonne 'categorie' existe déjà dans la table agents.</p>";
    } else {
        // Ajouter la colonne
        $db->exec("ALTER TABLE agents ADD COLUMN categorie ENUM('A', 'B', 'C') NULL COMMENT 'Catégorie de l\'agent' AFTER statut");
        echo "<p>La colonne 'categorie' a été ajoutée avec succès à la table agents.</p>";
    }
    
    echo "<p><a href='agents/create'>Retour au formulaire de création d'agent</a></p>";
    
} catch (Exception $e) {
    echo "<p>Erreur: " . $e->getMessage() . "</p>";
} 
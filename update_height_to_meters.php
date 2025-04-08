<?php
// Script pour mettre à jour les valeurs de taille de centimètres en mètres

// Inclure les fichiers nécessaires pour la connexion à la base de données
require_once 'bootstrap.php';

// Configuration de la connexion à la base de données
use app\Config\DB;

echo "Début de la conversion des tailles de centimètres en mètres...\n";

try {
    // 1. D'abord, créer une sauvegarde de la valeur actuelle
    echo "Étape 1: Sauvegarde des données actuelles...\n";
    DB::query("ALTER TABLE agents ADD COLUMN taille_backup DECIMAL(5,2)");
    DB::query("UPDATE agents SET taille_backup = taille WHERE taille IS NOT NULL");
    
    // 2. Convertir les valeurs de centimètres en mètres (diviser par 100)
    echo "Étape 2: Conversion des valeurs de centimètres en mètres...\n";
    DB::query("UPDATE agents SET taille = taille / 100 WHERE taille IS NOT NULL");
    
    // 3. Modifier la structure de la colonne pour qu'elle soit adaptée aux valeurs en mètres
    echo "Étape 3: Modification de la structure de la colonne...\n";
    DB::query("ALTER TABLE agents MODIFY COLUMN taille DECIMAL(3,2)");
    
    echo "Conversion terminée avec succès!\n";
    echo "Les anciennes valeurs ont été sauvegardées dans la colonne 'taille_backup'.\n";
    
} catch (Exception $e) {
    echo "Erreur lors de la conversion: " . $e->getMessage() . "\n";
    
    // En cas d'erreur, proposer des instructions pour restaurer
    echo "Pour restaurer les données originales, exécutez la commande SQL suivante:\n";
    echo "UPDATE agents SET taille = taille_backup WHERE taille_backup IS NOT NULL;\n";
} 
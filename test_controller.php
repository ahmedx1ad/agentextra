<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Tester l'accès au contrôleur
try {
    echo "Tentative de création d'une instance de RapportsController...\n";
    $controller = new app\Controllers\RapportsController();
    echo "Succès !\n";
    
    echo "Le contrôleur a été chargé avec succès !\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} 
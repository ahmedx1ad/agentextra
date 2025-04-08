<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Tester l'accès aux modèles
try {
    echo "Tentative de création d'une instance de Agent...\n";
    $agent = new app\models\Agent();
    echo "Succès !\n";
    
    echo "Tentative de création d'une instance de Service...\n";
    $service = new app\models\Service();
    echo "Succès !\n";
    
    echo "Tentative de création d'une instance de Responsable...\n";
    $responsable = new app\models\Responsable();
    echo "Succès !\n";
    
    echo "Tous les modèles ont été chargés avec succès !\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} 
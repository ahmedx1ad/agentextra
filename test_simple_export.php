<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Simuler une requête GET pour l'exportation
$_GET['entity_type'] = 'agents';
$_GET['format'] = 'csv';

// Créer une instance du contrôleur SimpleRapportsController
$controller = new app\Controllers\SimpleRapportsController();

// Appeler la méthode d'exportation
try {
    $controller->export();
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
} 
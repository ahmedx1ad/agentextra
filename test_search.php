<?php
// Test du SearchController

// Afficher toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le bootstrap de l'application
require_once __DIR__ . '/bootstrap.php';

echo "====== Test du SearchController ======\n\n";

try {
    // Créer une instance du contrôleur
    echo "Création de l'instance du SearchController...\n";
    $controller = new App\Controllers\SearchController();
    
    // Tester la recherche avec un terme
    echo "Test de performSearch('agent')...\n";
    $method = new ReflectionMethod('App\Controllers\SearchController', 'performSearch');
    $method->setAccessible(true);
    $results = $method->invoke($controller, 'agent');
    
    // Afficher les résultats
    echo "Résultats pour 'agent':\n";
    echo "Nombre d'agents trouvés: " . count($results['agents']) . "\n";
    echo "Nombre de services trouvés: " . count($results['services']) . "\n";
    echo "Nombre de responsables trouvés: " . count($results['responsables']) . "\n";
    
    if (!empty($results['agents'])) {
        echo "\nDétails du premier agent trouvé:\n";
        print_r(reset($results['agents']));
    }
    
    // Tester la méthode tableExists
    echo "\nTest de la méthode tableExists...\n";
    $method = new ReflectionMethod('App\Controllers\SearchController', 'tableExists');
    $method->setAccessible(true);
    
    $tables = ['agents', 'services', 'responsables', 'activity_logs', 'search_history'];
    foreach ($tables as $table) {
        $exists = $method->invoke($controller, $table);
        echo "Table '$table': " . ($exists ? "EXISTE" : "N'EXISTE PAS") . "\n";
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n====== Fin du test ======\n"; 
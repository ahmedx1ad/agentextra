<?php
// Script pour tester le DashboardController

// Afficher toutes les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le bootstrap de l'application
require_once __DIR__ . '/bootstrap.php';

echo "====== Test du DashboardController ======\n\n";

try {
    // Créer une instance du contrôleur
    echo "Création de l'instance du DashboardController...\n";
    $controller = new App\Controllers\DashboardController();
    
    // Tester getRecentAgents
    echo "Test de getRecentAgents()...\n";
    $method = new ReflectionMethod('App\Controllers\DashboardController', 'getRecentAgents');
    $method->setAccessible(true);
    $recentAgents = $method->invoke($controller);
    
    echo "Format des agents récents: " . (is_array($recentAgents) ? "ARRAY" : gettype($recentAgents)) . "\n";
    echo "Nombre d'agents récents: " . (is_array($recentAgents) ? count($recentAgents) : "N/A") . "\n";
    
    if (!empty($recentAgents)) {
        echo "Premier agent: " . print_r(reset($recentAgents), true) . "\n";
    }
    
    // Tester getRecentActivities
    echo "\nTest de getRecentActivities()...\n";
    $method = new ReflectionMethod('App\Controllers\DashboardController', 'getRecentActivities');
    $method->setAccessible(true);
    $recentActivities = $method->invoke($controller);
    
    echo "Format des activités récentes: " . (is_array($recentActivities) ? "ARRAY" : gettype($recentActivities)) . "\n";
    echo "Nombre d'activités récentes: " . (is_array($recentActivities) ? count($recentActivities) : "N/A") . "\n";
    
    if (!empty($recentActivities)) {
        echo "Première activité: " . print_r(reset($recentActivities), true) . "\n";
    }
    
    // Vérifier la compatibilité des données
    echo "\nVérification de la compatibilité des données...\n";
    
    // Simuler l'utilisation dans le template
    echo "Simulation de l'utilisation des agents dans le template...\n";
    
    if (!empty($recentAgents)) {
        foreach ($recentAgents as $agent) {
            echo "Accès aux propriétés d'un agent: ";
            
            try {
                if (is_object($agent)) {
                    $nom = isset($agent->nom) ? $agent->nom : "N/A";
                    $prenom = isset($agent->prenom) ? $agent->prenom : "N/A";
                    echo "Nom: $nom, Prénom: $prenom (accès via objet)\n";
                } else if (is_array($agent)) {
                    $nom = isset($agent['nom']) ? $agent['nom'] : "N/A";
                    $prenom = isset($agent['prenom']) ? $agent['prenom'] : "N/A";
                    echo "Nom: $nom, Prénom: $prenom (accès via tableau)\n";
                } else {
                    echo "Format non supporté: " . gettype($agent) . "\n";
                }
            } catch (Exception $e) {
                echo "ERREUR: " . $e->getMessage() . "\n";
            }
            
            break; // Ne tester que le premier agent
        }
    }
    
    // Vérifier conversion de tableau à objet
    echo "\nConversion de tableaux en objets et vice versa pour tests:\n";
    if (!empty($recentAgents)) {
        $agent = reset($recentAgents);
        
        // Conversion de l'objet en tableau si c'est un objet
        if (is_object($agent)) {
            echo "Conversion objet -> tableau...\n";
            $agentArray = json_decode(json_encode($agent), true);
            echo "Résultat: " . (is_array($agentArray) ? "SUCCÈS" : "ÉCHEC") . "\n";
            
            if (is_array($agentArray)) {
                echo "Accès aux propriétés via tableau: Nom: " . ($agentArray['nom'] ?? 'N/A') . "\n";
            }
        }
        
        // Conversion du tableau en objet si c'est un tableau
        if (is_array($agent)) {
            echo "Conversion tableau -> objet...\n";
            $agentObject = json_decode(json_encode($agent));
            echo "Résultat: " . (is_object($agentObject) ? "SUCCÈS" : "ÉCHEC") . "\n";
            
            if (is_object($agentObject)) {
                echo "Accès aux propriétés via objet: Nom: " . ($agentObject->nom ?? 'N/A') . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n====== Fin du test ======\n"; 
<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Créer une instance du modèle Agent
$agentModel = new app\models\Agent();

// Essayer de récupérer tous les agents
try {
    echo "Tentative de récupération des agents...\n";
    $agents = $agentModel->getAllWithFilters(['order_by' => 'performance', 'direction' => 'DESC']);
    
    if (empty($agents)) {
        echo "Aucun agent trouvé dans la base de données.\n";
    } else {
        echo "Nombre d'agents récupérés : " . count($agents) . "\n";
        echo "Voici les 3 premiers agents :\n";
        
        for ($i = 0; $i < min(3, count($agents)); $i++) {
            $agent = $agents[$i];
            echo "Agent #" . ($i+1) . ":\n";
            echo "  - Nom: " . ($agent->nom ?? 'N/A') . "\n";
            echo "  - Prénom: " . ($agent->prenom ?? 'N/A') . "\n";
            echo "  - Matricule: " . ($agent->matricule ?? 'N/A') . "\n";
            echo "  - Service: " . ($agent->service_nom ?? 'N/A') . "\n";
            echo "  - Performance: " . ($agent->performance ?? 'N/A') . "\n";
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "Erreur lors de la récupération des agents : " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
} 
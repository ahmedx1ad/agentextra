<?php
/**
 * Script pour mettre à jour automatiquement les performances de tous les agents
 * 
 * Usage:
 * - Exécution manuelle : php update_all_performances.php
 * - Configuration cron : 0 0 * * * php /chemin/vers/update_all_performances.php
 *   (cette configuration exécute le script une fois par jour à minuit)
 */

// Inclure les fichiers nécessaires pour la connexion à la base de données
require_once 'bootstrap.php';

// Configuration de la connexion à la base de données
use app\Config\DB;
use app\Models\Agent;

echo "=== Début de la mise à jour automatique des performances (" . date('Y-m-d H:i:s') . ") ===\n";

try {
    // Créer une instance du modèle Agent
    $agentModel = new Agent();
    
    // Récupérer tous les agents actifs
    $sql = "SELECT id FROM agents WHERE statut = 'actif'";
    $agents = DB::query($sql)->fetchAll(PDO::FETCH_OBJ);
    
    $total = count($agents);
    $success = 0;
    $failed = 0;
    
    echo "Nombre total d'agents à traiter: $total\n";
    
    // Mettre à jour les performances de chaque agent
    foreach ($agents as $agent) {
        echo "Traitement de l'agent ID {$agent->id}... ";
        
        if ($agentModel->calculatePerformance($agent->id)) {
            echo "OK\n";
            $success++;
        } else {
            echo "ÉCHEC\n";
            $failed++;
        }
    }
    
    echo "\nRésultats:\n";
    echo "- Agents traités avec succès: $success\n";
    echo "- Agents avec erreurs: $failed\n";
    echo "- Total: $total\n";
    
} catch (Exception $e) {
    echo "ERREUR CRITIQUE: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== Fin de la mise à jour (" . date('Y-m-d H:i:s') . ") ===\n";
exit(0); 
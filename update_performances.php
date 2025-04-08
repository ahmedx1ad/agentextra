<?php
// Script pour mettre à jour les performances des agents basées sur la nouvelle formule avec taille en mètres

// Inclure les fichiers nécessaires pour la connexion à la base de données
require_once 'bootstrap.php';

// Configuration de la connexion à la base de données
use app\Config\DB;

echo "Début de la mise à jour des performances des agents...\n";

try {
    // 1. D'abord, récupérer tous les agents avec leurs caractéristiques
    echo "Étape 1: Récupération des agents...\n";
    $agents = DB::query("
        SELECT a.id, a.matricule, a.nom, a.prenom, a.niveau_scolaire, 
               a.nombre_experience, a.taille, a.permis
        FROM agents a 
        WHERE a.statut = 'actif'
    ")->fetchAll(PDO::FETCH_OBJ);
    
    echo "Nombre d'agents trouvés: " . count($agents) . "\n";
    
    // 2. Calculer le score pour chaque agent avec la nouvelle formule
    echo "Étape 2: Calcul des nouveaux scores...\n";
    
    foreach ($agents as $agent) {
        // Calculer le score du niveau scolaire
        $niveau_score = 0;
        switch ($agent->niveau_scolaire) {
            case 'Bac+5': case 'master': $niveau_score = 5; break;
            case 'Bac+4': $niveau_score = 4; break;
            case 'Bac+3': case 'licence': $niveau_score = 3; break;
            case 'Bac+2': $niveau_score = 2; break;
            case 'Bac': case 'bac': $niveau_score = 1; break;
            default: $niveau_score = 0;
        }
        
        // Calculer le score global selon la nouvelle formule
        $taille = $agent->taille ?: 0;
        $experience = $agent->nombre_experience ?: 0;
        $permis_score = ($agent->permis && $agent->permis != 'NONE') ? 3 : 0;
        
        $score = ($niveau_score * 2) + ($experience * 1.5) + ($taille * 5) + $permis_score;
        
        // Normaliser le score sur 10
        $score_normalized = min(10, $score / 2);
        
        echo "Agent: {$agent->nom} {$agent->prenom} (ID: {$agent->id})\n";
        echo "  Taille: {$taille}m, Expérience: {$experience}, Niveau: {$agent->niveau_scolaire}, Permis: {$agent->permis}\n";
        echo "  Score calculé: {$score}, Score normalisé: {$score_normalized}\n";
        
        // 3. Mettre à jour ou insérer le score dans la table performances
        $check = DB::query("SELECT COUNT(*) FROM performances WHERE agent_id = ?", [$agent->id])->fetchColumn();
        
        if ($check > 0) {
            // Mettre à jour l'enregistrement existant
            $update = DB::query("
                UPDATE performances 
                SET performance = ?, 
                    evaluation_date = NOW(),
                    comments = 'Score mis à jour automatiquement avec nouvelle formule (taille en mètres)'
                WHERE agent_id = ?
            ", [$score_normalized, $agent->id]);
            
            echo "  Performance mise à jour.\n";
        } else {
            // Créer un nouvel enregistrement
            $insert = DB::query("
                INSERT INTO performances (agent_id, performance, evaluation_date, comments)
                VALUES (?, ?, NOW(), 'Score calculé automatiquement avec nouvelle formule (taille en mètres)')
            ", [$agent->id, $score_normalized]);
            
            echo "  Nouvelle performance créée.\n";
        }
    }
    
    echo "Mise à jour des performances terminée avec succès!\n";
    
} catch (Exception $e) {
    echo "Erreur lors de la mise à jour des performances: " . $e->getMessage() . "\n";
} 
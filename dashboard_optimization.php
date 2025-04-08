<?php
// Script d'optimisation du tableau de bord pour AgentExtra
require_once __DIR__ . '/bootstrap.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Optimisation du tableau de bord AgentExtra</h1>";

try {
    // Obtenir la connexion à la base de données
    $db = \App\Config\DB::getInstance();
    
    // Créer les tables d'optimisation si elles n'existent pas
    echo "<h2>Création de tables d'optimisation et de vues pour le tableau de bord</h2>";
    
    $optimizationQueries = [
        // Vue pour les statistiques des agents par service
        "CREATE OR REPLACE VIEW dashboard_agent_stats AS
         SELECT 
             s.id as service_id, 
             s.nom as service_nom,
             COUNT(a.id) as total_agents,
             SUM(CASE WHEN a.statut = 'actif' THEN 1 ELSE 0 END) as agents_actifs,
             SUM(CASE WHEN a.statut = 'inactif' THEN 1 ELSE 0 END) as agents_inactifs,
             AVG(a.experience) as experience_moyenne,
             MAX(a.date_recrutement) as dernier_recrutement
         FROM 
             services s
         LEFT JOIN 
             agents a ON s.id = a.service
         GROUP BY 
             s.id",
             
        // Table pour les statistiques globales du tableau de bord
        "CREATE TABLE IF NOT EXISTS dashboard_stats (
            id INT PRIMARY KEY AUTO_INCREMENT,
            total_agents INT NOT NULL DEFAULT 0,
            agents_actifs INT NOT NULL DEFAULT 0,
            agents_inactifs INT NOT NULL DEFAULT 0,
            total_services INT NOT NULL DEFAULT 0,
            total_responsables INT NOT NULL DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Vue pour les agents récents
        "CREATE OR REPLACE VIEW dashboard_recent_agents AS
         SELECT 
             a.id, 
             a.matricule,
             a.nom,
             a.prenom, 
             a.statut,
             a.date_recrutement,
             s.nom as service_nom,
             CONCAT(r.prenom, ' ', r.nom) as responsable_nom
         FROM 
             agents a
         LEFT JOIN 
             services s ON a.service = s.id
         LEFT JOIN 
             responsables r ON a.responsable_id = r.id
         ORDER BY 
             a.id DESC
         LIMIT 10"
    ];
    
    foreach ($optimizationQueries as $query) {
        try {
            $db->exec($query);
            echo "<p style='color:green'>✓ Requête exécutée avec succès: " . htmlspecialchars(substr($query, 0, 50)) . "...</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ Erreur lors de l'exécution de la requête: " . $e->getMessage() . "</p>";
        }
    }
    
    // Précalculer les statistiques globales
    echo "<h2>Précalcul des statistiques globales</h2>";
    
    // Vider d'abord la table des statistiques
    $db->exec("TRUNCATE TABLE dashboard_stats");
    
    // Calculer les statistiques globales
    $totalAgents = $db->query("SELECT COUNT(*) FROM agents")->fetchColumn();
    $agentsActifs = $db->query("SELECT COUNT(*) FROM agents WHERE statut = 'actif'")->fetchColumn();
    $agentsInactifs = $db->query("SELECT COUNT(*) FROM agents WHERE statut = 'inactif'")->fetchColumn();
    $totalServices = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
    $totalResponsables = $db->query("SELECT COUNT(*) FROM responsables")->fetchColumn();
    
    // Insérer les statistiques globales
    $stmt = $db->prepare("INSERT INTO dashboard_stats 
                         (total_agents, agents_actifs, agents_inactifs, total_services, total_responsables)
                         VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$totalAgents, $agentsActifs, $agentsInactifs, $totalServices, $totalResponsables]);
    
    echo "<p style='color:green'>✓ Statistiques globales précalculées avec succès</p>";
    
    // Créer une procédure stockée pour mettre à jour les statistiques
    echo "<h2>Création de procédures stockées pour les mises à jour rapides</h2>";
    
    $procedures = [
        "DROP PROCEDURE IF EXISTS update_dashboard_stats",
        
        "CREATE PROCEDURE update_dashboard_stats()
        BEGIN
            TRUNCATE TABLE dashboard_stats;
            INSERT INTO dashboard_stats 
            (total_agents, agents_actifs, agents_inactifs, total_services, total_responsables)
            VALUES (
                (SELECT COUNT(*) FROM agents),
                (SELECT COUNT(*) FROM agents WHERE statut = 'actif'),
                (SELECT COUNT(*) FROM agents WHERE statut = 'inactif'),
                (SELECT COUNT(*) FROM services),
                (SELECT COUNT(*) FROM responsables)
            );
        END"
    ];
    
    foreach ($procedures as $procedure) {
        try {
            $db->exec($procedure);
            echo "<p style='color:green'>✓ Procédure exécutée avec succès</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ Erreur lors de l'exécution de la procédure: " . $e->getMessage() . "</p>";
        }
    }
    
    // Créer un événement MySQL pour mettre à jour les statistiques régulièrement
    echo "<h2>Création d'un événement pour la mise à jour automatique des statistiques</h2>";
    
    $events = [
        "SET GLOBAL event_scheduler = ON",
        
        "DROP EVENT IF EXISTS update_dashboard_stats_event",
        
        "CREATE EVENT update_dashboard_stats_event
        ON SCHEDULE EVERY 30 MINUTE
        DO CALL update_dashboard_stats()"
    ];
    
    foreach ($events as $event) {
        try {
            $db->exec($event);
            echo "<p style='color:green'>✓ Événement créé avec succès</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ Erreur lors de la création de l'événement: " . $e->getMessage() . "</p>";
            echo "<p>Cette erreur est normale si les événements ne sont pas activés sur votre serveur MySQL. Les statistiques seront mises à jour manuellement.</p>";
        }
    }
    
    // Vider le cache de l'application
    echo "<h2>Nettoyage du cache de l'application</h2>";
    
    $cacheDir = __DIR__ . '/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*.cache');
        $count = 0;
        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }
        echo "<p style='color:green'>✓ $count fichiers de cache supprimés</p>";
    } else {
        echo "<p style='color:orange'>⚠ Dossier de cache non trouvé, il sera créé automatiquement lors de la prochaine utilisation</p>";
    }
    
    echo "<h2>Résumé</h2>";
    echo "<p>L'optimisation du tableau de bord a été effectuée avec succès. Voici ce qui a été réalisé :</p>";
    echo "<ul>";
    echo "<li>Création de vues optimisées pour les requêtes fréquentes</li>";
    echo "<li>Précalcul des statistiques globales</li>";
    echo "<li>Création d'une procédure stockée pour la mise à jour</li>";
    echo "<li>Configuration d'un événement pour la mise à jour automatique (si supporté)</li>";
    echo "<li>Nettoyage du cache</li>";
    echo "</ul>";
    
    echo "<p style='margin-top:20px;'><a href='dashboard' class='btn btn-primary' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Aller au tableau de bord</a></p>";
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>Erreur lors de l'optimisation du tableau de bord</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 
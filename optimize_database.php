<?php
// Script d'optimisation de la base de données pour AgentExtra
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Optimisation de la base de données AgentExtra</h1>";

try {
    // Connexion à la base de données
    require_once __DIR__ . '/bootstrap.php';
    $db = \App\Config\DB::getInstance();
    
    echo "<h2>Ajout d'index pour améliorer les performances</h2>";
    
    // Liste des index à ajouter
    $indexes = [
        // Table agents - Indexes pour accélérer les recherches et filtres
        "CREATE INDEX IF NOT EXISTS idx_agents_nom ON agents (nom)",
        "CREATE INDEX IF NOT EXISTS idx_agents_prenom ON agents (prenom)",
        "CREATE INDEX IF NOT EXISTS idx_agents_service ON agents (service)",
        "CREATE INDEX IF NOT EXISTS idx_agents_statut ON agents (statut)",
        "CREATE INDEX IF NOT EXISTS idx_agents_responsable ON agents (responsable_id)",
        "CREATE INDEX IF NOT EXISTS idx_agents_date_recrutement ON agents (date_recrutement)",
        "CREATE INDEX IF NOT EXISTS idx_agents_niveau ON agents (niveau)",
        "CREATE INDEX IF NOT EXISTS idx_agents_experience ON agents (experience)",
        
        // Table services - Index pour accélérer les recherches
        "CREATE INDEX IF NOT EXISTS idx_services_nom ON services (nom)",
        
        // Table responsables - Indexes pour accélérer les recherches
        "CREATE INDEX IF NOT EXISTS idx_responsables_nom ON responsables (nom)",
        "CREATE INDEX IF NOT EXISTS idx_responsables_prenom ON responsables (prenom)",
        
        // Table users - Indexes pour accélérer la connexion et les recherches
        "CREATE INDEX IF NOT EXISTS idx_users_email ON users (email)",
        "CREATE INDEX IF NOT EXISTS idx_users_name ON users (name)"
    ];
    
    // Exécution des requêtes de création d'index
    $successCount = 0;
    foreach ($indexes as $index) {
        try {
            $db->exec($index);
            echo "<p style='color:green'>✓ Index créé avec succès : " . htmlspecialchars($index) . "</p>";
            $successCount++;
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ Erreur lors de la création de l'index : " . htmlspecialchars($index) . " - " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Analyse des tables pour la mise à jour des statistiques</h2>";
    
    // Liste des tables à analyser
    $tables = ['agents', 'services', 'responsables', 'users'];
    
    foreach ($tables as $table) {
        try {
            $db->exec("ANALYZE TABLE $table");
            echo "<p style='color:green'>✓ Analyse de la table '$table' effectuée avec succès</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ Erreur lors de l'analyse de la table '$table' : " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Optimisation des tables</h2>";
    
    foreach ($tables as $table) {
        try {
            $db->exec("OPTIMIZE TABLE $table");
            echo "<p style='color:green'>✓ Optimisation de la table '$table' effectuée avec succès</p>";
        } catch (\PDOException $e) {
            echo "<p style='color:orange'>⚠ L'optimisation de la table '$table' a échoué, tentative d'optimisation alternative</p>";
            try {
                // Alternative pour MySQL InnoDB qui ne supporte pas directement OPTIMIZE TABLE
                $db->exec("ALTER TABLE $table ENGINE=InnoDB");
                echo "<p style='color:green'>✓ Optimisation alternative de la table '$table' effectuée</p>";
            } catch (\PDOException $e2) {
                echo "<p style='color:red'>✗ Échec de l'optimisation alternative : " . $e2->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h2>Résumé</h2>";
    echo "<p>$successCount index ont été créés ou vérifiés avec succès.</p>";
    echo "<p>Les tables de la base de données ont été analysées et optimisées.</p>";
    echo "<p>Ces modifications devraient améliorer significativement les performances du tableau de bord et des listes.</p>";
    
    echo "<h2>Actions recommandées</h2>";
    echo "<ol>";
    echo "<li>Videz le cache de l'application</li>";
    echo "<li>Redémarrez le serveur web si possible</li>";
    echo "<li>Testez les performances du tableau de bord</li>";
    echo "</ol>";
    
    echo "<p><a href='dashboard' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Aller au tableau de bord</a></p>";
    
} catch (\Exception $e) {
    echo "<h2 style='color:red'>Erreur lors de l'optimisation de la base de données</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 
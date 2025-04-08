<?php
/**
 * Script d'optimisation automatique pour AgentExtra
 * Peut être ajouté comme tâche cron pour s'exécuter régulièrement
 * Exemple de configuration cron (chaque jour à 3h du matin) : 0 3 * * * php /path/to/optimize_cron.php
 */

// Mode silencieux pour l'exécution cron
$isCli = (php_sapi_name() === 'cli');
$silent = $isCli || (isset($_GET['silent']) && $_GET['silent'] === '1');

// Si exécuté en ligne de commande, définir le chemin correct
if ($isCli) {
    chdir(dirname(__FILE__));
}

// Charger le bootstrap de l'application
require_once __DIR__ . '/bootstrap.php';

// Fonction pour afficher ou journaliser les messages
function log_message($message, $level = 'info') {
    global $silent, $isCli;
    
    // Journaliser le message
    error_log("[AgentExtra Optimizer] [$level] $message");
    
    // Afficher le message si nécessaire
    if (!$silent) {
        $color = '';
        $reset = '';
        
        if ($isCli) {
            // Couleurs pour l'affichage en ligne de commande
            switch ($level) {
                case 'success': $color = "\033[32m"; $reset = "\033[0m"; break; // Vert
                case 'error': $color = "\033[31m"; $reset = "\033[0m"; break; // Rouge
                case 'warning': $color = "\033[33m"; $reset = "\033[0m"; break; // Jaune
                default: break;
            }
            echo $color . $message . $reset . PHP_EOL;
        } else {
            // Formatage HTML pour l'affichage dans le navigateur
            switch ($level) {
                case 'success': echo "<p style='color:green'>✓ $message</p>"; break;
                case 'error': echo "<p style='color:red'>✗ $message</p>"; break;
                case 'warning': echo "<p style='color:orange'>⚠ $message</p>"; break;
                default: echo "<p>$message</p>"; break;
            }
        }
    }
}

if (!$silent && !$isCli) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>AgentExtra - Optimisation Programmée</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
            h1, h2 { color: #333; }
            .success { color: green; }
            .error { color: red; }
            .warning { color: orange; }
        </style>
    </head>
    <body>
        <h1>AgentExtra - Optimisation Programmée</h1>";
}

try {
    // Obtenir la connexion à la base de données
    $db = \App\Config\DB::getInstance();
    
    log_message("Début de l'optimisation automatique de l'application AgentExtra", 'info');
    
    // 1. Mettre à jour les statistiques du tableau de bord
    log_message("1. Mise à jour des statistiques du tableau de bord", 'info');
    
    try {
        // Vérifier si la table des statistiques existe
        $stmt = $db->query("SHOW TABLES LIKE 'dashboard_stats'");
        if ($stmt->rowCount() > 0) {
            // Vérifier si la procédure stockée existe
            $stmt = $db->query("SHOW PROCEDURE STATUS WHERE Name = 'update_dashboard_stats'");
            if ($stmt->rowCount() > 0) {
                // Utiliser la procédure stockée
                $db->exec("CALL update_dashboard_stats()");
                log_message("Statistiques mises à jour via la procédure stockée", 'success');
            } else {
                // Mettre à jour manuellement
                $db->exec("TRUNCATE TABLE dashboard_stats");
                $stmt = $db->prepare("
                    INSERT INTO dashboard_stats 
                    (total_agents, agents_actifs, agents_inactifs, total_services, total_responsables)
                    VALUES (
                        (SELECT COUNT(*) FROM agents),
                        (SELECT COUNT(*) FROM agents WHERE statut = 'actif'),
                        (SELECT COUNT(*) FROM agents WHERE statut = 'inactif'),
                        (SELECT COUNT(*) FROM services),
                        (SELECT COUNT(*) FROM responsables)
                    )
                ");
                $stmt->execute();
                log_message("Statistiques mises à jour manuellement", 'success');
            }
        } else {
            log_message("La table dashboard_stats n'existe pas. Exécutez d'abord dashboard_optimization.php", 'warning');
        }
    } catch (\Exception $e) {
        log_message("Erreur lors de la mise à jour des statistiques: " . $e->getMessage(), 'error');
    }
    
    // 2. Optimiser les tables de la base de données
    log_message("2. Optimisation des tables de la base de données", 'info');
    
    $tables = ['agents', 'services', 'responsables', 'users', 'activities', 'settings'];
    
    foreach ($tables as $table) {
        try {
            $db->exec("ANALYZE TABLE $table");
            log_message("Table '$table' analysée", 'success');
            
            try {
                $db->exec("OPTIMIZE TABLE $table");
                log_message("Table '$table' optimisée", 'success');
            } catch (\Exception $e) {
                log_message("Impossible d'optimiser la table '$table'. Tentative alternative...", 'warning');
                try {
                    $db->exec("ALTER TABLE $table ENGINE=InnoDB");
                    log_message("Table '$table' optimisée via ALTER TABLE", 'success');
                } catch (\Exception $e2) {
                    log_message("Échec de l'optimisation alternative pour '$table': " . $e2->getMessage(), 'error');
                }
            }
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                log_message("La table '$table' n'existe pas. Ignorée.", 'warning');
            } else {
                log_message("Erreur lors de l'analyse de la table '$table': " . $e->getMessage(), 'error');
            }
        }
    }
    
    // 3. Nettoyer le cache
    log_message("3. Nettoyage du cache expiré", 'info');
    
    $cacheDir = __DIR__ . '/cache';
    $now = time();
    $expiredCount = 0;
    
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                try {
                    $content = file_get_contents($file);
                    $cache = unserialize($content);
                    
                    // Vérifier si l'entrée de cache est expirée depuis plus de 24 heures
                    if (isset($cache['expires']) && $cache['expires'] < ($now - 86400)) {
                        unlink($file);
                        $expiredCount++;
                    }
                } catch (\Exception $e) {
                    // Si le fichier est invalide, le supprimer
                    unlink($file);
                    $expiredCount++;
                }
            }
        }
        
        log_message("$expiredCount fichiers de cache expirés supprimés", 'success');
    } else {
        log_message("Le dossier de cache n'existe pas encore", 'warning');
    }
    
    // 4. Nettoyer les fichiers temporaires
    log_message("4. Nettoyage des fichiers temporaires", 'info');
    
    $tmpDir = __DIR__ . '/tmp';
    $tmpCount = 0;
    
    if (is_dir($tmpDir)) {
        $files = glob($tmpDir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                // Supprimer les fichiers temporaires de plus de 7 jours
                if (filemtime($file) < ($now - 7 * 86400)) {
                    unlink($file);
                    $tmpCount++;
                }
            }
        }
        
        log_message("$tmpCount fichiers temporaires supprimés", 'success');
    } else {
        log_message("Le dossier tmp n'existe pas", 'warning');
    }
    
    // 5. Vérifier l'espace disque
    log_message("5. Vérification de l'espace disque", 'info');
    
    $diskFree = disk_free_space('/');
    $diskTotal = disk_total_space('/');
    $diskUsed = $diskTotal - $diskFree;
    $diskPercent = round(($diskUsed / $diskTotal) * 100, 2);
    
    $diskFreeFormatted = formatSize($diskFree);
    $diskTotalFormatted = formatSize($diskTotal);
    
    log_message("Espace disque libre: $diskFreeFormatted / $diskTotalFormatted ($diskPercent% utilisé)", 'info');
    
    if ($diskPercent > 90) {
        log_message("L'espace disque est presque plein! Veuillez libérer de l'espace.", 'warning');
    }
    
    log_message("Optimisation terminée avec succès!", 'success');
    
} catch (\Exception $e) {
    log_message("Erreur critique lors de l'optimisation: " . $e->getMessage(), 'error');
}

// Fonction pour formater la taille en Ko, Mo, Go
function formatSize($size) {
    $units = ['o', 'Ko', 'Mo', 'Go', 'To', 'Po'];
    $power = floor(log($size, 1024));
    return round($size / pow(1024, $power), 2) . ' ' . $units[$power];
}

if (!$silent && !$isCli) {
    echo "</body></html>";
}
?> 
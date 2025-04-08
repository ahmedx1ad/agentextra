<?php
/**
 * Script pour nettoyer le cache du tableau de bord
 * À exécuter après des mises à jour importantes ou pour résoudre des problèmes de performance
 */

// Afficher les erreurs en mode développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le bootstrap de l'application
require_once __DIR__ . '/bootstrap.php';

// Charger le helper de cache
use app\helpers\CacheHelper;

echo "=== Nettoyage du cache du tableau de bord ===\n\n";

// Nettoyer seulement le cache du tableau de bord
if (isset($argv[1]) && $argv[1] === 'dashboard') {
    CacheHelper::invalidateDashboardCache();
    echo "Le cache du tableau de bord a été vidé avec succès.\n";
}
// Nettoyer le cache des agents
else if (isset($argv[1]) && $argv[1] === 'agents') {
    CacheHelper::invalidateAgentsCache();
    echo "Le cache des agents a été vidé avec succès.\n";
}
// Nettoyer le cache des services
else if (isset($argv[1]) && $argv[1] === 'services') {
    CacheHelper::invalidateServicesCache();
    echo "Le cache des services a été vidé avec succès.\n";
}
// Nettoyer le cache des responsables
else if (isset($argv[1]) && $argv[1] === 'responsables') {
    CacheHelper::invalidateResponsablesCache();
    echo "Le cache des responsables a été vidé avec succès.\n";
}
// Nettoyer tout le cache
else {
    CacheHelper::clearAllCache();
    echo "Tout le cache de l'application a été vidé avec succès.\n";
}

echo "\nLe tableau de bord devrait maintenant être plus rapide.\n";
echo "Si vous rencontrez encore des problèmes de performance, vérifiez :\n";
echo "- La configuration MySQL dans my.ini\n";
echo "- Les index de la base de données\n";
echo "- La taille des requêtes SQL\n";

echo "\n=== Opération terminée ===\n";
?> 
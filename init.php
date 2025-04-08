<?php
/**
 * Fichier d'initialisation pour la compatibilité avec l'ancien code.
 * Redirige vers le bootstrap principal pour une configuration unifiée.
 */

// Charger le bootstrap principal
require_once __DIR__ . '/bootstrap.php';

// Définir BASE_URL pour la compatibilité avec l'ancien code si nécessaire
if (!defined('BASE_URL')) {
    define('BASE_URL', '/agentextra');
}

// Note: Ce fichier est maintenu pour la compatibilité et sera progressivement déprécié. 
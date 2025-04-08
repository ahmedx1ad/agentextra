<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// S'assurer que la session est démarrée
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Simuler une requête GET pour l'exportation avec redirection
$_GET['entity_type'] = 'agents';
$_GET['format'] = 'csv';
$_GET['redirect'] = 'true';

// Simuler la requête HTTP
$_SERVER['HTTP_REFERER'] = 'http://localhost/agentextra/agents/simple-selection';

// Créer une instance du contrôleur SimpleRapportsController
$controller = new app\Controllers\SimpleRapportsController();

// Journaliser l'état de la session avant l'exportation
error_log("Session ID avant export: " . session_id());
error_log("Contenu de la session avant export: " . json_encode($_SESSION));

// Appeler la méthode d'exportation
try {
    // Capturer la sortie
    ob_start();
    $controller->export();
    $output = ob_get_clean();
    
    // Si nous arrivons ici, c'est que la redirection n'a pas fonctionné
    echo "ERREUR: La redirection n'a pas fonctionné.\n";
    echo "Sortie: " . $output . "\n";
    
    // Vérifier l'état de la session
    echo "Session ID après export: " . session_id() . "\n";
    echo "Contenu de la session après export: " . json_encode($_SESSION) . "\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
} 
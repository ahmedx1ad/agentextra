<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// S'assurer que la session est démarrée
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Définir un message de succès dans la session
$_SESSION['success'] = "Ceci est un message de test pour vérifier que les sessions fonctionnent correctement.";

echo "Message de succès défini dans la session.\n";
echo "Session ID: " . session_id() . "\n";
echo "Contenu de la session: " . json_encode($_SESSION) . "\n";

// Rediriger vers la page de sélection simplifiée
echo "Redirection vers la page de sélection simplifiée...\n";
echo "Veuillez visiter manuellement l'URL suivante pour voir si le message s'affiche :\n";
echo "http://localhost/agentextra/agents/simple-selection\n"; 
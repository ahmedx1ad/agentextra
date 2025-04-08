<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Vérifier si la session est active
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session active: " . session_id() . "\n";
} else {
    echo "Session inactive\n";
    session_start();
    echo "Session démarrée: " . session_id() . "\n";
}

// Définir un message de test
$_SESSION['test_message'] = "Test de session à " . date('Y-m-d H:i:s');
echo "Message défini dans la session\n";

// Afficher toutes les variables de session
echo "\nContenu de la session:\n";
print_r($_SESSION);

echo "\nChemin du cookie de session: " . session_save_path() . "\n";
echo "Nom du cookie de session: " . session_name() . "\n";
echo "ID de session: " . session_id() . "\n"; 
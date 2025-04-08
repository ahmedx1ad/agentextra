<?php
/**
 * Script de test pour vérifier la création d'un responsable
 */

// Inclure les fichiers nécessaires
require_once 'bootstrap.php';

echo "=== Test de création d'un responsable ===\n\n";

// Simuler la connexion d'un utilisateur
$_SESSION['user_id'] = 1;

// Simuler les données POST du formulaire
$_POST = [
    'csrf_token' => bin2hex(random_bytes(32)), // Juste pour simulation
    'nom' => 'Test',
    'prenom' => 'Responsable',
    'email' => 'test.resp' . time() . '@example.com',
    'telephone' => '0654321987',
    'service_id' => 1,
    'ville' => 'Test City',
    'matricule' => 'TEST' . rand(1000, 9999)
];

// Sauvegarder le jeton CSRF simulé dans la session
$_SESSION['csrf_token'] = $_POST['csrf_token'];

echo "Simulation création responsable: " . $_POST['nom'] . " " . $_POST['prenom'] . "\n";
echo "Email: " . $_POST['email'] . "\n";
echo "Matricule: " . $_POST['matricule'] . "\n\n";

try {
    // Créer une instance du contrôleur
    $db = \app\Config\DB::getInstance();
    $controller = new \app\Controllers\ResponsablesController($db);
    
    // Appeler la méthode create
    echo "Appel de la méthode create...\n";
    $controller->create();
    
    echo "\nVérification du résultat :\n";
    
    // Vérifier si le responsable a été créé
    $stmt = $db->prepare("SELECT * FROM responsables WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($responsable) {
        echo "✅ Succès! Responsable créé avec ID: " . $responsable['id'] . "\n";
        echo "Données enregistrées:\n";
        print_r($responsable);
    } else {
        echo "❌ Échec! Aucun responsable créé.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n"; 
<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "==== Script de test pour la création d'agents et de responsables ====\n\n";

try {
    // Tester la connexion à la base de données
    echo "Vérification de la connexion à la base de données... ";
    $db = App\Config\DB::getInstance();
    $result = $db->query("SELECT 1")->fetchColumn();
    echo ($result == 1) ? "OK\n" : "ÉCHEC\n";
    
    echo "\n----- Création d'un responsable -----\n";
    
    // Instancier le contrôleur de responsables
    $responsableController = new App\Controllers\ResponsablesController();
    
    // Créer un responsable test
    $matricule = 'RESP' . rand(1000, 9999);
    $nom = 'Test';
    $prenom = 'Responsable' . rand(1, 100);
    $email = strtolower($prenom) . '.' . strtolower($nom) . '@test.com';
    
    // Préparer les données du responsable
    $_POST = [
        'matricule' => $matricule,
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'telephone' => '0123456789',
        'date_embauche' => date('Y-m-d'),
        'service_id' => 1, // Assurez-vous qu'un service avec ID 1 existe
    ];
    
    // Simuler la création du responsable
    echo "Création du responsable: $prenom $nom ($matricule)...\n";
    
    // Vérifier si le responsable existe déjà
    $stmt = $db->prepare("SELECT id FROM responsables WHERE matricule = ?");
    $stmt->execute([$matricule]);
    if ($stmt->rowCount() > 0) {
        echo "Un responsable avec ce matricule existe déjà, suppression préalable...\n";
        $stmt = $db->prepare("DELETE FROM responsables WHERE matricule = ?");
        $stmt->execute([$matricule]);
    }
    
    // Créer le responsable directement via la base de données
    $stmt = $db->prepare("
        INSERT INTO responsables (matricule, nom, prenom, email, telephone, date_embauche, service_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $result = $stmt->execute([
        $matricule,
        $nom,
        $prenom,
        $email,
        '0123456789',
        date('Y-m-d'),
        1 // ID du service
    ]);
    
    if ($result) {
        $responsableId = $db->lastInsertId();
        echo "Responsable créé avec succès! ID: $responsableId\n";
    } else {
        echo "Échec de la création du responsable\n";
    }
    
    echo "\n----- Création d'un agent -----\n";
    
    // Instancier le contrôleur d'agents
    $agentController = new App\Controllers\AgentController();
    
    // Créer un agent test
    $matricule = 'AGENT' . rand(10000, 99999);
    $nom = 'Test';
    $prenom = 'Agent' . rand(1, 100);
    
    // Vérifier si l'agent existe déjà
    $stmt = $db->prepare("SELECT id FROM agents WHERE matricule = ?");
    $stmt->execute([$matricule]);
    if ($stmt->rowCount() > 0) {
        echo "Un agent avec ce matricule existe déjà, suppression préalable...\n";
        $stmt = $db->prepare("DELETE FROM agents WHERE matricule = ?");
        $stmt->execute([$matricule]);
    }
    
    // Créer l'agent directement via la base de données
    echo "Création de l'agent: $prenom $nom ($matricule)...\n";
    $stmt = $db->prepare("
        INSERT INTO agents (matricule, nom, prenom, service_id, responsable_id, statut, date_creation)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $matricule,
        $nom,
        $prenom,
        1, // ID du service
        $responsableId, // ID du responsable créé précédemment
        'actif'
    ]);
    
    if ($result) {
        $agentId = $db->lastInsertId();
        echo "Agent créé avec succès! ID: $agentId\n";
    } else {
        echo "Échec de la création de l'agent\n";
    }
    
    echo "\n----- Test de fonctionnement de SearchController -----\n";
    
    // Instancier le contrôleur de recherche
    $searchController = new App\Controllers\SearchController();
    
    // Effectuer une recherche
    echo "Recherche du nouveau responsable et agent...\n";
    $searchResults = $searchController->search($nom);
    
    // Afficher les résultats
    echo "Résultats de la recherche pour '$nom':\n";
    echo "Agents trouvés: " . count($searchResults['agents']) . "\n";
    echo "Responsables trouvés: " . count($searchResults['responsables']) . "\n";
    
    echo "\n==== Test terminé avec succès ====\n";
    
} catch (Exception $e) {
    echo "\n*** ERREUR: " . $e->getMessage() . " ***\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
} 
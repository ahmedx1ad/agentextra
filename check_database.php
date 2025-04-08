<?php
// Outil de vérification de la base de données unifiée
require_once __DIR__ . '/bootstrap.php';
use app\Config\DB;

echo "=== VÉRIFICATION DE LA BASE DE DONNÉES AGENTEXTRA ===" . PHP_EOL;
echo "Date d'exécution: " . date('Y-m-d H:i:s') . PHP_EOL;
echo PHP_EOL;

try {
    // 1. Vérifier la connexion à la base de données
    echo "1. Test de connexion à la base de données... ";
    $db = DB::getInstance();
    echo "SUCCÈS" . PHP_EOL;
    
    // 2. Vérifier la présence des tables requises
    echo "2. Vérification des tables requises..." . PHP_EOL;
    $requiredTables = ['users', 'services', 'responsables', 'agents', 'performances', 'user_favorites'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        echo "   - Table '$table': ";
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            echo "MANQUANTE" . PHP_EOL;
            $missingTables[] = $table;
        } else {
            echo "TROUVÉE" . PHP_EOL;
        }
    }
    
    if (!empty($missingTables)) {
        echo PHP_EOL . "ATTENTION: Les tables suivantes sont manquantes: " . implode(', ', $missingTables) . PHP_EOL;
        echo "Veuillez exécuter le script database_setup.sql pour créer ces tables." . PHP_EOL;
    }
    
    // 3. Vérifier les données existantes
    echo PHP_EOL . "3. Vérification des données existantes..." . PHP_EOL;
    
    $tables = [
        'users' => 'Utilisateurs',
        'services' => 'Services',
        'responsables' => 'Responsables',
        'agents' => 'Agents',
        'performances' => 'Évaluations de performance',
        'user_favorites' => 'Favoris utilisateur'
    ];
    
    foreach ($tables as $table => $description) {
        if (in_array($table, $missingTables)) {
            continue; // Ne pas vérifier les données pour les tables manquantes
        }
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM $table");
        $result = $stmt->fetch();
        $count = $result->total;
        
        echo "   - $description: $count enregistrement(s)" . PHP_EOL;
    }
    
    // 4. Vérifier les permissions de l'utilisateur MySQL
    echo PHP_EOL . "4. Vérification des permissions MySQL..." . PHP_EOL;
    
    try {
        // Test d'insertion
        $db->beginTransaction();
        $db->query("INSERT INTO services (nom, description) VALUES ('Test Service', 'Test Service Description')");
        $serviceId = $db->lastInsertId();
        echo "   - Permission INSERT: SUCCÈS" . PHP_EOL;
        
        // Test de mise à jour
        $stmt = $db->prepare("UPDATE services SET nom = 'Test Service Updated' WHERE id = ?");
        $stmt->execute([$serviceId]);
        echo "   - Permission UPDATE: SUCCÈS" . PHP_EOL;
        
        // Test de suppression
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        echo "   - Permission DELETE: SUCCÈS" . PHP_EOL;
        
        // Annuler toutes les modifications
        $db->rollback();
        echo "   - Permission ROLLBACK: SUCCÈS" . PHP_EOL;
    } catch (Exception $e) {
        echo "   - ERREUR: " . $e->getMessage() . PHP_EOL;
        
        if ($db->inTransaction()) {
            $db->rollback();
        }
    }
    
    // 5. Vérifier la performance
    echo PHP_EOL . "5. Test de performance de la base de données..." . PHP_EOL;
    
    $start = microtime(true);
    $db->query("SELECT * FROM services LIMIT 100");
    $end = microtime(true);
    $time = round(($end - $start) * 1000, 2);
    
    echo "   - Durée d'exécution d'une requête simple: $time ms" . PHP_EOL;
    
    if ($time > 100) {
        echo "   - ATTENTION: La requête a pris plus de 100ms, ce qui est relativement lent." . PHP_EOL;
    } else {
        echo "   - Performance acceptable." . PHP_EOL;
    }
    
    // Conclusion
    echo PHP_EOL . "=== VÉRIFICATION TERMINÉE ===" . PHP_EOL;
    
    if (empty($missingTables)) {
        echo "La base de données est correctement configurée et accessible." . PHP_EOL;
    } else {
        echo "La base de données présente des problèmes qui doivent être corrigés." . PHP_EOL;
        echo "Veuillez exécuter le script database_setup.sql pour créer les tables manquantes." . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    
    echo PHP_EOL . "VÉRIFICATIONS À EFFECTUER:" . PHP_EOL;
    echo "1. Assurez-vous que le service MySQL est démarré" . PHP_EOL;
    echo "2. Vérifiez les informations de connexion dans app/Config/DB.php" . PHP_EOL;
    echo "3. Vérifiez que la base de données 'agentextra' existe" . PHP_EOL;
    echo "4. Assurez-vous que l'utilisateur MySQL a les permissions nécessaires" . PHP_EOL;
} 
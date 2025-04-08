<?php
// Ne pas redéfinir APP_ENV s'il est déjà défini
if (!defined('APP_ENV')) {
    define('APP_ENV', 'development');
}

// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

echo "====== Test de connexion à la base de données ======\n\n";

try {
    // Informations sur l'environnement
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Extensions PDO chargées: " . implode(', ', PDO::getAvailableDrivers()) . "\n\n";
    
    echo "Tentative de connexion à la base de données...\n";
    
    // Récupérer les informations de configuration
    $host = 'localhost';
    $dbname = 'agentextra';
    $username = 'root';
    $password = '';
    
    echo "Paramètres de connexion:\n";
    echo "Hôte: $host\n";
    echo "Base de données: $dbname\n";
    echo "Utilisateur: $username\n";
    echo "Mot de passe: " . (empty($password) ? "(vide)" : "(défini)") . "\n\n";
    
    // Test d'une connexion directe
    echo "Test de connexion PDO directe: ";
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $directPdo = new PDO($dsn, $username, $password, $options);
    echo "SUCCÈS\n\n";
    
    // Test via le singleton
    echo "Test de connexion via le singleton DB: ";
    $db = App\Config\DB::getInstance();
    $result = $db->query("SELECT 1 as test")->fetch();
    
    if ($result && isset($result['test']) && $result['test'] == 1) {
        echo "SUCCÈS\n\n";
    } else {
        echo "ÉCHEC - Réponse incorrecte de la base de données\n\n";
    }
    
    // Vérifier les tables existantes
    echo "Tables existantes dans la base de données:\n";
    $tables = $directPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "Aucune table trouvée. La base de données existe-t-elle réellement?\n";
    } else {
        foreach ($tables as $index => $table) {
            echo ($index + 1) . ". $table\n";
        }
    }
    
} catch (PDOException $e) {
    echo "ERREUR DE CONNEXION PDO: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "ERREUR GÉNÉRALE: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n====== Fin du test ======\n"; 
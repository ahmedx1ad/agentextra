<?php
// Script d'installation des fonctionnalités professionnelles
echo "===== Installation des fonctionnalités professionnelles =====\n";

// Charger la configuration
$config = require_once __DIR__ . '/config/config.php';

try {
    // Établir la connexion à la base de données
    $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['name']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['user'], $config['database']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    
    echo "✅ Connexion à la base de données établie.\n";
    
    // Utiliser le fichier SQL global au lieu des migrations individuelles
    $globalSqlFile = 'app/database/global_database.sql';
    
    if (file_exists($globalSqlFile)) {
        $sql = file_get_contents($globalSqlFile);
        echo "⏳ Exécution du fichier SQL global...\n";
        
        // Exécuter chaque instruction séparément
        $statements = explode(';', $sql);
        $executedCount = 0;
        $totalCount = count($statements);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $executedCount++;
                } catch (PDOException $e) {
                    // Ignorer les erreurs comme les tables qui existent déjà
                    echo "⚠️ Avertissement: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "✅ Fichier SQL global exécuté avec succès ($executedCount/$totalCount requêtes)\n";
    } else {
        echo "❌ Fichier SQL global non trouvé: {$globalSqlFile}\n";
        
        // Proposer d'utiliser le script d'installation dédié
        echo "ℹ️ Utilisez le script dédié pour initialiser la base de données:\n";
        echo "php app/database/setup_database.php\n";
        exit(1);
    }
    
    echo "\n===== Création des répertoires nécessaires =====\n";
    
    // Créer les répertoires pour les vues
    $directories = [
        'app/views/competences',
        'app/views/formations'
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            echo "⏳ Création du répertoire: {$dir}...\n";
            if (mkdir($dir, 0755, true)) {
                echo "✅ Répertoire créé avec succès: {$dir}\n";
            } else {
                echo "❌ Impossible de créer le répertoire: {$dir}\n";
            }
        } else {
            echo "ℹ️ Le répertoire existe déjà: {$dir}\n";
        }
    }
    
    echo "\n===== Installation terminée avec succès =====\n";
    echo "Vous pouvez maintenant accéder aux nouvelles fonctionnalités professionnelles depuis le menu.\n";
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Erreur lors de l'installation: " . $e->getMessage() . "\n";
    exit(1); 
}
?> 
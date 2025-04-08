<?php
/**
 * Script de diagnostic MySQL pour AgentExtra
 * 
 * Ce script vérifie si le serveur MySQL est accessible et opérationnel.
 * Il peut être utilisé pour diagnostiquer les problèmes de connexion à la base de données.
 */

// Configuration de la base de données (utiliser les mêmes valeurs que dans votre application)
$host = 'localhost';     // Hôte MySQL (généralement localhost)
$port = 3306;            // Port MySQL standard
$user = 'root';          // Nom d'utilisateur MySQL par défaut de XAMPP
$pass = '';              // Mot de passe MySQL par défaut de XAMPP (vide)
$dbname = 'agentextra';  // Nom de votre base de données

echo "=================================================\n";
echo "      DIAGNOSTIC MYSQL POUR AGENTEXTRA          \n";
echo "=================================================\n\n";

// Étape 1: Vérifier si le serveur MySQL est accessible
echo "1. Vérification de l'accessibilité du serveur MySQL... ";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);

if (!$socket) {
    echo "ÉCHEC\n";
    echo "   Erreur: Le serveur MySQL n'est pas accessible sur $host:$port\n";
    echo "   Message: $errstr (code: $errno)\n\n";
    echo "   Solutions possibles:\n";
    echo "   - Vérifiez que le service MySQL est démarré dans XAMPP Control Panel\n";
    echo "   - Vérifiez que le port 3306 n'est pas bloqué par un pare-feu\n";
    echo "   - Assurez-vous qu'aucun autre service n'utilise le port 3306\n\n";
    die("Diagnostic terminé avec des erreurs. Veuillez corriger les problèmes ci-dessus.\n");
} else {
    echo "OK\n";
    fclose($socket);
}

// Étape 2: Tenter une connexion à MySQL
echo "2. Tentative de connexion à MySQL... ";
try {
    $dsn = "mysql:host=$host;port=$port";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "OK\n";
} catch (PDOException $e) {
    echo "ÉCHEC\n";
    echo "   Erreur: Impossible de se connecter à MySQL\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
    echo "   Solutions possibles:\n";
    echo "   - Vérifiez vos identifiants MySQL (utilisateur/mot de passe)\n";
    echo "   - Vérifiez les permissions de l'utilisateur MySQL\n\n";
    die("Diagnostic terminé avec des erreurs. Veuillez corriger les problèmes ci-dessus.\n");
}

// Étape 3: Vérifier si la base de données existe
echo "3. Vérification de l'existence de la base de données '$dbname'... ";
try {
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $result = $stmt->fetchColumn();
    
    if ($result) {
        echo "OK\n";
    } else {
        echo "ÉCHEC\n";
        echo "   Erreur: La base de données '$dbname' n'existe pas\n";
        echo "   Solutions possibles:\n";
        echo "   - Créez la base de données dans phpMyAdmin\n";
        echo "   - Vérifiez l'orthographe du nom de la base de données\n\n";
        die("Diagnostic terminé avec des erreurs. Veuillez corriger les problèmes ci-dessus.\n");
    }
} catch (PDOException $e) {
    echo "ÉCHEC\n";
    echo "   Erreur lors de la vérification de la base de données\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
    die("Diagnostic terminé avec des erreurs. Veuillez corriger les problèmes ci-dessus.\n");
}

// Étape 4: Tenter une connexion à la base de données
echo "4. Connexion à la base de données '$dbname'... ";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $db_pdo = new PDO($dsn, $user, $pass);
    $db_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "OK\n";
} catch (PDOException $e) {
    echo "ÉCHEC\n";
    echo "   Erreur: Impossible de se connecter à la base de données '$dbname'\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
    die("Diagnostic terminé avec des erreurs. Veuillez corriger les problèmes ci-dessus.\n");
}

// Étape 5: Vérifier les tables nécessaires
echo "5. Vérification des tables de l'application... ";
$requiredTables = ['agents', 'services', 'responsables', 'users', 'performances'];
$missingTables = [];

try {
    $stmt = $db_pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($requiredTables as $table) {
        if (!in_array($table, $tables)) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        echo "OK\n";
    } else {
        echo "AVERTISSEMENT\n";
        echo "   Les tables suivantes sont manquantes: " . implode(', ', $missingTables) . "\n";
        echo "   Solutions possibles:\n";
        echo "   - Importez le fichier SQL du schéma de la base de données\n";
        echo "   - Exécutez le script d'initialisation de la base de données\n\n";
    }
} catch (PDOException $e) {
    echo "ERREUR\n";
    echo "   Erreur lors de la vérification des tables\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

// Étape 6: Vérifier les variables de configuration MySQL
echo "6. Vérification des variables MySQL importantes... ";
try {
    $stmt = $db_pdo->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $maxPacket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $db_pdo->query("SHOW VARIABLES LIKE 'wait_timeout'");
    $waitTimeout = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Affichage des informations
    echo "OK\n\n";
    echo "Informations MySQL:\n";
    echo "- Version: " . $db_pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "- max_allowed_packet: " . ($maxPacket['Value'] / (1024*1024)) . " MB\n";
    echo "- wait_timeout: " . $waitTimeout['Value'] . " secondes\n\n";
    
    // Recommandations
    if ($maxPacket['Value'] < (16 * 1024 * 1024)) {
        echo "RECOMMANDATION: Augmentez max_allowed_packet à au moins 16 MB\n";
    }
    
    if ($waitTimeout['Value'] < 300) {
        echo "RECOMMANDATION: Augmentez wait_timeout à au moins 300 secondes\n";
    }
    
} catch (PDOException $e) {
    echo "ERREUR\n";
    echo "   Erreur lors de la vérification des variables MySQL\n";
    echo "   Message: " . $e->getMessage() . "\n\n";
}

echo "\n=================================================\n";
echo "      DIAGNOSTIC MYSQL TERMINÉ AVEC SUCCÈS       \n";
echo "=================================================\n\n";

echo "Votre serveur MySQL est accessible et opérationnel.\n";
echo "Si vous rencontrez toujours des problèmes, vérifiez :\n";
echo "1. Le fichier de configuration de l'application\n";
echo "2. Les erreurs dans les journaux MySQL\n";
echo "3. Les problèmes réseau ou de pare-feu\n\n";
echo "Consultez le fichier mysql_optimization.txt pour des recommandations détaillées.\n\n"; 
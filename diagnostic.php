<?php
// Script de diagnostic pour le système d'authentification

// Initialiser la sortie du diagnostic
$diagnostic = [];
$has_errors = false;

// Désactiver l'affichage des erreurs pour éviter d'afficher des informations sensibles
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Capture des erreurs pour le diagnostic
function add_diagnostic($type, $title, $message, $is_error = false) {
    global $diagnostic, $has_errors;
    $diagnostic[] = [
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'is_error' => $is_error
    ];
    if ($is_error) {
        $has_errors = true;
    }
}

// Paramètres de connexion à la base de données
$host = '127.0.0.1';
$port = '3306';
$dbname = 'agentextra';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

// 1. Vérifier la connexion au serveur MySQL
try {
    $dsn = "mysql:host={$host};port={$port};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    add_diagnostic('success', 'Connexion au serveur MySQL', 'Connexion réussie');
    
    // 2. Vérifier l'existence de la base de données
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
    if ($stmt->rowCount() === 0) {
        add_diagnostic('error', 'Base de données', "La base de données '{$dbname}' n'existe pas", true);
    } else {
        add_diagnostic('success', 'Base de données', "La base de données '{$dbname}' existe");
        
        // Se connecter à la base de données spécifique
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}", $username, $password, $options);
        
        // 3. Vérifier l'existence de la table users
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() === 0) {
            add_diagnostic('error', 'Table users', "La table 'users' n'existe pas", true);
        } else {
            add_diagnostic('success', 'Table users', "La table 'users' existe");
            
            // 4. Vérifier la structure de la table users
            $stmt = $pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['id', 'email', 'username', 'password', 'role', 'is_active'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (count($missing_columns) > 0) {
                add_diagnostic('warning', 'Structure de la table users', 
                    "Certaines colonnes requises sont manquantes: " . implode(', ', $missing_columns));
            } else {
                add_diagnostic('success', 'Structure de la table users', 
                    "Toutes les colonnes requises sont présentes");
            }
            
            // 5. Vérifier les utilisateurs existants
            $stmt = $pdo->query("SELECT id, email, username, role, is_active FROM users");
            $users = $stmt->fetchAll();
            
            if (count($users) === 0) {
                add_diagnostic('error', 'Utilisateurs', "Aucun utilisateur n'existe dans la base de données", true);
            } else {
                add_diagnostic('success', 'Utilisateurs', "Nombre d'utilisateurs: " . count($users));
                
                // 6. Vérifier le test spécifique avec les identifiants fournis par l'utilisateur
                $test_login = 'test2024';
                $test_password = '2025@2025a';
                
                // Rechercher l'utilisateur
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
                $stmt->execute([$test_login, $test_login]);
                $test_user = $stmt->fetch();
                
                if (!$test_user) {
                    add_diagnostic('error', 'Utilisateur de test',
                        "L'utilisateur '$test_login' n'existe pas dans la base de données", true);
                } else {
                    add_diagnostic('success', 'Utilisateur de test', 
                        "L'utilisateur '$test_login' existe (ID: {$test_user['id']})");
                    
                    // Vérifier le mot de passe
                    if (password_verify($test_password, $test_user['password'])) {
                        add_diagnostic('success', 'Mot de passe de test', 
                            "Le mot de passe pour '$test_login' est correct");
                    } else {
                        add_diagnostic('error', 'Mot de passe de test',
                            "Le mot de passe pour '$test_login' est incorrect", true);
                    }
                    
                    // Vérifier si le compte est actif
                    if ($test_user['is_active'] == 1) {
                        add_diagnostic('success', 'Statut du compte', "Le compte '$test_login' est actif");
                    } else {
                        add_diagnostic('error', 'Statut du compte',
                            "Le compte '$test_login' est désactivé", true);
                    }
                }
            }
        }
    }
    
} catch (PDOException $e) {
    add_diagnostic('error', 'Connexion base de données', 
        "Erreur de connexion: " . $e->getMessage(), true);
} catch (Exception $e) {
    add_diagnostic('error', 'Erreur générale', $e->getMessage(), true);
}

// Afficher les résultats du diagnostic
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic d'authentification - AgentExtra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #f8f9fa; 
            padding: 20px;
        }
        .diagnostic-card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            font-weight: bold;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }
        .info { background-color: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Diagnostic d'authentification - AgentExtra</h1>
        
        <?php if ($has_errors): ?>
            <div class="alert alert-danger mb-4">
                <h4 class="alert-heading">Des problèmes ont été détectés!</h4>
                <p>Le diagnostic a révélé des erreurs qui peuvent empêcher la connexion.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-success mb-4">
                <h4 class="alert-heading">Tout semble en ordre!</h4>
                <p>Le diagnostic n'a révélé aucun problème majeur.</p>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <?php foreach ($diagnostic as $item): ?>
                <div class="col-12">
                    <div class="card diagnostic-card">
                        <div class="card-header <?= $item['type'] ?>">
                            <?= htmlspecialchars($item['title']) ?>
                        </div>
                        <div class="card-body">
                            <p class="card-text"><?= htmlspecialchars($item['message']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <h3 class="mt-4 mb-3">Informations techniques</h3>
        <div class="card mb-4">
            <div class="card-body">
                <p><strong>Version PHP:</strong> <?= phpversion() ?></p>
                <p><strong>PDO drivers:</strong> <?= implode(', ', PDO::getAvailableDrivers()) ?></p>
                <p><strong>Extensions chargées:</strong> <?= implode(', ', array_slice(get_loaded_extensions(), 0, 10)) ?>...</p>
                <p><strong>Paramètres de connexion:</strong> host=<?= $host ?>, port=<?= $port ?>, dbname=<?= $dbname ?>, user=<?= $username ?></p>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="create_test_user.php" class="btn btn-primary me-md-2">Créer/Mettre à jour les utilisateurs de test</a>
            <a href="index.php" class="btn btn-success">Retour à la page de connexion</a>
        </div>
    </div>
</body>
</html> 
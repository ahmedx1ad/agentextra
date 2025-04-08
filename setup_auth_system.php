<?php
// Installation du système d'authentification amélioré
// Permet la connexion par email ou nom d'utilisateur

// Vérifier que le script est exécuté depuis le navigateur
if (php_sapi_name() === 'cli') {
    die("Ce script doit être exécuté depuis un navigateur.\n");
}

// Paramètres de connexion
$host = 'localhost';
$db   = 'agentextra';
$user = 'root';
$pass = '';

// Affichage du début de l'installation
echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation du système d\'authentification amélioré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 800px; margin-top: 30px; }
        .log-entry { margin-bottom: 8px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Installation du système d\'authentification amélioré</h1>
        <div class="card">
            <div class="card-header">
                <h5>Journal d\'installation</h5>
            </div>
            <div class="card-body">
                <div id="log">';

function log_message($message, $type = 'info') {
    echo '<div class="log-entry ' . $type . '">' . htmlspecialchars($message) . '</div>';
    ob_flush();
    flush();
}

log_message("Démarrage de l'installation...", 'info');

try {
    // Connexion à la base de données
    log_message("Tentative de connexion à la base de données...", 'info');
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    log_message("Connexion à la base de données réussie.", 'success');
    
    // 1. Vérifier si les colonnes existent déjà
    log_message("Vérification des colonnes dans la table users...", 'info');
    $columnsToCheck = ['email', 'username', 'updated_at'];
    $existingColumns = [];
    
    foreach ($columnsToCheck as $column) {
        $stmt = $pdo->query("SELECT COUNT(*) as column_exists 
                            FROM information_schema.columns 
                            WHERE table_schema = '$db' 
                            AND table_name = 'users' 
                            AND column_name = '$column'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ((int)$row['column_exists'] > 0) {
            $existingColumns[] = $column;
            log_message("La colonne '$column' existe déjà dans la table users.", 'info');
        }
    }
    
    // 2. Ajouter les colonnes manquantes
    if (!in_array('email', $existingColumns)) {
        log_message("Ajout de la colonne 'email'...", 'info');
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) NULL AFTER `password_hash`");
        log_message("Colonne 'email' ajoutée avec succès.", 'success');
    }
    
    if (!in_array('username', $existingColumns)) {
        log_message("Ajout de la colonne 'username'...", 'info');
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `email`");
        log_message("Colonne 'username' ajoutée avec succès.", 'success');
    }
    
    if (!in_array('updated_at', $existingColumns)) {
        log_message("Ajout de la colonne 'updated_at'...", 'info');
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        log_message("Colonne 'updated_at' ajoutée avec succès.", 'success');
    }
    
    // 3. Ajouter des index uniques
    log_message("Configuration des index uniques...", 'info');
    try {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_email` (`email`)");
        log_message("Index unique pour 'email' ajouté avec succès.", 'success');
    } catch (PDOException $e) {
        log_message("Note: " . $e->getMessage(), 'warning');
    }
    
    try {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE INDEX `idx_username` (`username`)");
        log_message("Index unique pour 'username' ajouté avec succès.", 'success');
    } catch (PDOException $e) {
        log_message("Note: " . $e->getMessage(), 'warning');
    }
    
    // 4. Mise à jour des utilisateurs existants
    log_message("Mise à jour des utilisateurs existants...", 'info');
    $stmt = $pdo->query("SELECT id, first_name, last_name FROM users WHERE username IS NULL");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        log_message("Génération de noms d'utilisateurs pour " . count($users) . " utilisateurs...", 'info');
        
        $updateStmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        
        foreach ($users as $user) {
            // Créer un nom d'utilisateur basé sur le prénom et le nom
            $baseUsername = strtolower(transliterator_transliterate(
                'Any-Latin; Latin-ASCII; [^a-zA-Z0-9] Remove; Lower()',
                $user['first_name'] . '.' . $user['last_name']
            ));
            
            // Vérifier si ce nom d'utilisateur existe déjà
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $checkStmt->execute([$baseUsername]);
            $usernameExists = (bool) $checkStmt->fetchColumn();
            
            // Si le nom d'utilisateur existe, ajouter un suffixe numérique
            $finalUsername = $baseUsername;
            $counter = 1;
            
            while ($usernameExists && $counter < 100) {
                $finalUsername = $baseUsername . $counter;
                $checkStmt->execute([$finalUsername]);
                $usernameExists = (bool) $checkStmt->fetchColumn();
                $counter++;
            }
            
            // Mettre à jour l'utilisateur
            $updateStmt->execute([$finalUsername, $user['id']]);
            log_message("Utilisateur #" . $user['id'] . " mis à jour avec le nom d'utilisateur: " . $finalUsername, 'success');
        }
    } else {
        log_message("Aucun utilisateur n'a besoin d'être mis à jour.", 'info');
    }
    
    log_message("Installation terminée avec succès!", 'success');
    log_message("Vous pouvez maintenant vous connecter avec votre nom d'utilisateur ou votre email.", 'info');
    
} catch (PDOException $e) {
    log_message("Erreur: " . $e->getMessage(), 'error');
}

// Affichage de la fin de l'installation
echo '            </div>
            </div>
            <div class="card-footer">
                <a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>
            </div>
        </div>
    </div>
</body>
</html>';
?> 
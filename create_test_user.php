<?php
// Script pour créer un utilisateur de test pour AgentExtra
// À exécuter pour résoudre le problème de connexion

// Charger le bootstrap s'il existe
if (file_exists(__DIR__ . '/bootstrap.php')) {
    require_once __DIR__ . '/bootstrap.php';
}

// Paramètres de connexion à la base de données
$host = '127.0.0.1';
$port = '3306';
$dbname = 'agentextra';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

echo "<h1>Création d'un utilisateur de test pour AgentExtra</h1>";

try {
    // Connexion à la base de données
    $dsn = "mysql:host={$host};port={$port};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "<p style='color:green'>✓ Connexion à MySQL réussie!</p>";
    
    // Vérifier si la base de données existe
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
    if ($stmt->rowCount() === 0) {
        echo "<p>La base de données '{$dbname}' n'existe pas. Création en cours...</p>";
        $pdo->exec("CREATE DATABASE `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color:green'>✓ Base de données '{$dbname}' créée!</p>";
    } else {
        echo "<p>La base de données '{$dbname}' existe déjà.</p>";
    }
    
    // Se connecter à la base de données spécifique
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}", $username, $password, $options);
    
    // Vérifier si la table users existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        echo "<p>La table 'users' n'existe pas. Création en cours...</p>";
        
        // Créer la table users avec la colonne username
        $pdo->exec("CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `username` varchar(50) DEFAULT NULL,
            `password` varchar(255) NOT NULL,
            `role` enum('admin','user') DEFAULT 'user',
            `first_name` varchar(50) DEFAULT NULL,
            `last_name` varchar(50) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `is_active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `username` (`username`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p style='color:green'>✓ Table 'users' créée avec la colonne username!</p>";
    } else {
        // Vérifier si la colonne username existe
        $columnExists = false;
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $columnExists = count($result) > 0;
        
        if (!$columnExists) {
            echo "<p>Ajout de la colonne 'username' à la table 'users'...</p>";
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) AFTER email");
            $pdo->exec("ALTER TABLE users ADD UNIQUE KEY `username` (`username`)");
            echo "<p style='color:green'>✓ Colonne 'username' ajoutée!</p>";
        }
    }
    
    // Créer l'utilisateur de test avec des identifiants connus
    $testUser = [
        'email' => 'test2025',
        'username' => 'test2025',
        'password' => password_hash('2025@2025', PASSWORD_DEFAULT),
        'role' => 'admin',
        'first_name' => 'Utilisateur',
        'last_name' => 'Test',
        'is_active' => 1
    ];
    
    // Vérifier si l'utilisateur test existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$testUser['email']]);
    
    if ($stmt->rowCount() > 0) {
        // Mettre à jour l'utilisateur existant avec le nouveau mot de passe et nom d'utilisateur
        $sql = "UPDATE users SET username = ?, password = ?, role = ?, first_name = ?, last_name = ?, is_active = ? WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $testUser['username'],
            $testUser['password'],
            $testUser['role'],
            $testUser['first_name'],
            $testUser['last_name'],
            $testUser['is_active'],
            $testUser['email']
        ]);
        
        echo "<p style='color:green'>✓ Utilisateur de test mis à jour avec nom d'utilisateur!</p>";
    } else {
        // Créer le nouvel utilisateur
        $sql = "INSERT INTO users (email, username, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $testUser['email'],
            $testUser['username'],
            $testUser['password'],
            $testUser['role'],
            $testUser['first_name'],
            $testUser['last_name'],
            $testUser['is_active']
        ]);
        
        echo "<p style='color:green'>✓ Utilisateur de test créé avec nom d'utilisateur!</p>";
    }
    
    // Créer également l'utilisateur Admin par défaut
    $adminUser = [
        'email' => 'admin@example.com',
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'admin',
        'first_name' => 'Administrateur',
        'last_name' => '',
        'is_active' => 1
    ];
    
    // Vérifier si l'admin existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$adminUser['email']]);
    
    if ($stmt->rowCount() === 0) {
        // Créer l'utilisateur admin
        $sql = "INSERT INTO users (email, username, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $adminUser['email'],
            $adminUser['username'],
            $adminUser['password'],
            $adminUser['role'],
            $adminUser['first_name'],
            $adminUser['last_name'],
            $adminUser['is_active']
        ]);
        
        echo "<p style='color:green'>✓ Utilisateur admin créé avec nom d'utilisateur!</p>";
    } else {
        // Mettre à jour l'utilisateur admin existant avec un nom d'utilisateur
        $sql = "UPDATE users SET username = ? WHERE email = ? AND (username IS NULL OR username = '')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$adminUser['username'], $adminUser['email']]);
        
        echo "<p>L'utilisateur admin existe déjà, nom d'utilisateur mis à jour si nécessaire.</p>";
    }
    
    // Créer également un utilisateur test2024 puisque c'est celui que l'utilisateur essaie d'utiliser
    $test2024User = [
        'email' => 'test2024',
        'username' => 'test2024',
        'password' => password_hash('2025@2025a', PASSWORD_DEFAULT),
        'role' => 'admin',
        'first_name' => 'Test',
        'last_name' => '2024',
        'is_active' => 1
    ];
    
    // Vérifier si l'utilisateur test2024 existe déjà
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$test2024User['email'], $test2024User['username']]);
    
    if ($stmt->rowCount() > 0) {
        // Mettre à jour l'utilisateur existant
        $sql = "UPDATE users SET password = ?, role = ?, first_name = ?, last_name = ?, is_active = ? WHERE email = ? OR username = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $test2024User['password'],
            $test2024User['role'],
            $test2024User['first_name'],
            $test2024User['last_name'],
            $test2024User['is_active'],
            $test2024User['email'],
            $test2024User['username']
        ]);
        
        echo "<p style='color:green'>✓ Utilisateur test2024 mis à jour!</p>";
    } else {
        // Créer le nouvel utilisateur
        $sql = "INSERT INTO users (email, username, password, role, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $test2024User['email'],
            $test2024User['username'],
            $test2024User['password'],
            $test2024User['role'],
            $test2024User['first_name'],
            $test2024User['last_name'],
            $test2024User['is_active']
        ]);
        
        echo "<p style='color:green'>✓ Utilisateur test2024 créé!</p>";
    }
    
    echo "<div style='margin-top: 20px; padding: 15px; background-color: #e6f7ff; border: 1px solid #b3e0ff; border-radius: 5px;'>";
    echo "<h2>Identifiants de connexion</h2>";
    
    echo "<div style='padding: 10px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 10px;'>";
    echo "<p><strong>⭐ Identifiants correspondant à votre tentative de connexion :</strong></p>";
    echo "<p><strong>Email ou nom d'utilisateur :</strong> test2024</p>";
    echo "<p><strong>Mot de passe :</strong> 2025@2025a</p>";
    echo "</div>";
    
    echo "<p><strong>Email ou nom d'utilisateur :</strong> test2025</p>";
    echo "<p><strong>Mot de passe :</strong> 2025@2025</p>";
    echo "<hr>";
    echo "<p><strong>Email :</strong> admin@example.com</p>";
    echo "<p><strong>Nom d'utilisateur :</strong> admin</p>";
    echo "<p><strong>Mot de passe :</strong> admin123</p>";
    echo "</div>";
    
    echo "<p style='margin-top: 20px;'><a href='index.php' style='padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Retour à la page de connexion</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}
?> 
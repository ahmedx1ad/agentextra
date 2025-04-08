<?php
require_once 'bootstrap.php';
require_once 'config/database.php';

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Recréer la table
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('admin', 'manager', 'agent') NOT NULL DEFAULT 'agent',
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        is_active BOOLEAN DEFAULT TRUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Créer les utilisateurs avec des hachages frais
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $managerHash = password_hash('manager123', PASSWORD_DEFAULT);
    $agentHash = password_hash('agent123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute(['admin@example.com', $adminHash, 'admin', 'Admin', 'System']);
    $stmt->execute(['manager@example.com', $managerHash, 'manager', 'Manager', 'User']);
    $stmt->execute(['agent@example.com', $agentHash, 'agent', 'Agent', 'Simple']);
    
    echo "Utilisateurs créés avec succès!<br>";
    echo "admin@example.com / admin123<br>";
    echo "manager@example.com / manager123<br>";
    echo "agent@example.com / agent123<br>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
} 
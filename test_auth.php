<?php
require_once 'bootstrap.php';
require_once 'config/database.php';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
    ]);
    echo "<p>Connexion à la base de données réussie.</p>";
} catch (PDOException $e) {
    die("<p>Erreur de connexion : " . $e->getMessage() . "</p>");
}

// Test de récupération des utilisateurs
try {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    echo "<p>Nombre d'utilisateurs trouvés : " . count($users) . "</p>";
    
    echo "<table border='1'>
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Rôle</th>
        <th>Nom</th>
        <th>Hash (10 premiers caractères)</th>
        <th>Test mot de passe</th>
    </tr>";
    
    foreach ($users as $user) {
        // Test de password_verify pour admin123
        $testPassword = 'admin123';
        $passwordVerified = password_verify($testPassword, $user->password_hash);
        
        echo "<tr>
            <td>{$user->id}</td>
            <td>{$user->email}</td>
            <td>{$user->role}</td>
            <td>{$user->first_name} {$user->last_name}</td>
            <td>" . substr($user->password_hash, 0, 10) . "...</td>
            <td>" . ($passwordVerified ? "OK" : "NON") . " pour '$testPassword'</td>
        </tr>";
    }
    
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
} 
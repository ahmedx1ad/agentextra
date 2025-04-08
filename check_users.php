<?php
// Script temporaire pour vérifier les utilisateurs

// Paramètres de connexion
$host = 'localhost';
$db   = 'agentextra';
$user = 'root';
$pass = '';

echo '<h1>Liste des utilisateurs</h1>';
echo '<table border="1" cellpadding="5">';
echo '<tr><th>ID</th><th>Nom d\'utilisateur</th><th>Email</th><th>Nom complet</th></tr>';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les utilisateurs
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name FROM users");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['username'] ?? 'Non défini') . '</td>';
        echo '<td>' . htmlspecialchars($row['email'] ?? 'Non défini') . '</td>';
        echo '<td>' . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . '</td>';
        echo '</tr>';
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="4">Erreur: ' . $e->getMessage() . '</td></tr>';
}

echo '</table>';

?> 
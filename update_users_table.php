<?php
try {
    // Connexion à la base de données
    $db = new PDO('mysql:host=127.0.0.1;dbname=agentextra;port=3306', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la colonne username existe déjà
    $columnExists = false;
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'username'");
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnExists = count($result) > 0;
    
    if ($columnExists) {
        echo "<div style='background: #d1e7dd; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h2>La colonne 'username' existe déjà</h2>";
        echo "<p>La colonne existe déjà dans la table users. Aucune modification nécessaire.</p>";
        echo "</div>";
    } else {
        // Ajouter la colonne username
        $db->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) AFTER email");
        
        // Mettre à jour les valeurs de username pour les utilisateurs existants
        // On utilise les 5 premiers caractères de l'email ou le prénom comme nom d'utilisateur
        $db->exec("UPDATE users SET username = 
                   CASE 
                       WHEN first_name IS NOT NULL AND first_name != '' THEN first_name
                       ELSE SUBSTRING_INDEX(email, '@', 1)
                   END");
        
        echo "<div style='background: #d1e7dd; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h2>Colonne 'username' ajoutée avec succès</h2>";
        echo "<p>La colonne username a été ajoutée à la table users et des valeurs par défaut ont été générées.</p>";
        echo "</div>";
    }
    
    // Afficher la structure mise à jour
    $stmt = $db->query('DESCRIBE users');
    echo "<h2>Structure actuelle de la table users</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $style = ($row['Field'] === 'username') ? "background-color: #e8f4fd;" : "";
        echo "<tr style='$style'>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Afficher les utilisateurs avec leurs nouveaux noms d'utilisateur
    $stmt = $db->query("SELECT id, email, username, first_name, last_name FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<h2>Liste des utilisateurs</h2>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Email</th><th>Nom d'utilisateur</th><th>Prénom</th><th>Nom</th></tr>";
        
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td style='background-color: #e8f4fd;'>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['first_name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['last_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<div style='margin: 20px 0;'>";
    echo "<p><a href='/' style='display: inline-block; padding: 10px 15px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page d'accueil</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo '<div style="background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px;">';
    echo '<h2>Erreur de base de données</h2>';
    echo '<p>Erreur: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
?> 
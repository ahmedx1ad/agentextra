<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Charger les fichiers nécessaires
require_once __DIR__ . '/app/Config/config.php';
require_once __DIR__ . '/app/Config/DB.php';
require_once __DIR__ . '/app/helpers.php';

// Définir le chemin d'inclusion automatique des classes
spl_autoload_register(function ($class) {
    $file = str_replace('\\', '/', $class) . '.php';
    if (file_exists(__DIR__ . '/' . $file)) {
        require_once __DIR__ . '/' . $file;
        return true;
    }
    return false;
});

// Créer un en-tête HTML simple
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Responsable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test de Responsable</h1>';

try {
    // Récupérer l'ID depuis l'URL ou utiliser 1 par défaut
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
    
    echo '<div class="alert alert-info">Test de récupération du responsable avec ID: ' . $id . '</div>';
    
    // Instancier le modèle
    $responsableModel = new \app\Models\Responsable();
    
    // Tester la méthode findById
    echo '<h2>Test de findById</h2>';
    $responsable = $responsableModel->findById($id);
    
    if ($responsable) {
        echo '<div class="alert alert-success">Responsable trouvé!</div>';
        echo '<h3>Données du responsable:</h3>';
        echo '<pre>';
        var_dump($responsable);
        echo '</pre>';
        
        echo '<table class="table table-bordered">';
        echo '<tr><th>Propriété</th><th>Valeur</th></tr>';
        foreach ($responsable as $key => $value) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($key) . '</td>';
            echo '<td>' . htmlspecialchars(is_string($value) ? $value : var_export($value, true)) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        // Tester l'édition
        echo '<a href="' . BASE_URL . '/responsables/edit/' . $responsable->id . '" class="btn btn-primary">Éditer ce responsable</a>';
    } else {
        echo '<div class="alert alert-danger">Aucun responsable trouvé avec cet ID.</div>';
        
        // Compter les responsables
        $count = \app\Config\DB::query("SELECT COUNT(*) FROM responsables")->fetchColumn();
        echo '<p>Nombre total de responsables dans la base: ' . $count . '</p>';
        
        if ($count > 0) {
            $ids = \app\Config\DB::query("SELECT id FROM responsables LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
            echo '<p>IDs disponibles: ' . implode(', ', $ids) . '</p>';
            
            if (!empty($ids)) {
                echo '<p>Essayez avec un de ces IDs:</p>';
                echo '<ul>';
                foreach ($ids as $availableId) {
                    echo '<li><a href="test_responsable.php?id=' . $availableId . '">Tester avec ID=' . $availableId . '</a></li>';
                }
                echo '</ul>';
            }
        } else {
            echo '<div class="alert alert-warning">La table des responsables est vide. Vous devez d\'abord ajouter un responsable.</div>';
            echo '<a href="' . BASE_URL . '/responsables/add" class="btn btn-success">Créer un responsable</a>';
        }
    }
    
    // Vérifier la structure de la table
    echo '<h2>Structure de la table responsables</h2>';
    $columns = \app\Config\DB::query("SHOW COLUMNS FROM responsables")->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table class="table table-sm table-striped">';
    echo '<thead><tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr></thead>';
    echo '<tbody>';
    foreach ($columns as $column) {
        echo '<tr>';
        echo '<td>' . $column['Field'] . '</td>';
        echo '<td>' . $column['Type'] . '</td>';
        echo '<td>' . $column['Null'] . '</td>';
        echo '<td>' . $column['Key'] . '</td>';
        echo '<td>' . $column['Default'] . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">';
    echo '<h3>Erreur:</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
    echo '</div>';
}

// Fin du HTML
echo '
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>'; 
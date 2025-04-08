<?php
// Script pour tester l'accès aux fichiers CSS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définir chemin de base
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/agentextra';
$css_files = [
    'dashboard.css',
    'search.css',
    'style.css'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'accès aux fichiers CSS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #4361ee;
        }
        .test-result {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d1e7dd;
            border: 1px solid #badbcc;
            color: #0f5132;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
        }
        code {
            display: block;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e2e3e5;
            border-radius: 4px;
            margin: 10px 0;
        }
        .css-display {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #e2e3e5;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h1>Test d'accès aux fichiers CSS</h1>
    
    <?php foreach ($css_files as $file): ?>
        <?php
        $url = "{$base_url}/public/assets/css/{$file}";
        $headers = get_headers($url, 1);
        $status_code = intval(substr($headers[0], 9, 3));
        $content_type = isset($headers['Content-Type']) ? $headers['Content-Type'] : 'Non spécifié';
        $status_class = ($status_code === 200) ? 'success' : 'error';
        ?>
        
        <div class="test-result <?php echo $status_class; ?>">
            <h3>Fichier: <?php echo htmlspecialchars($file); ?></h3>
            <p><strong>URL:</strong> <?php echo htmlspecialchars($url); ?></p>
            <p><strong>Code de statut:</strong> <?php echo $status_code; ?></p>
            <p><strong>Type de contenu:</strong> <?php echo htmlspecialchars($content_type); ?></p>
            
            <?php if ($status_code === 200): ?>
                <p><a href="<?php echo htmlspecialchars($url); ?>" target="_blank">Ouvrir le fichier directement</a></p>
            <?php else: ?>
                <p>Le fichier n'est pas accessible.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <h2>Solution alternative (liens directs vers les CSS)</h2>
    <p>Ajoutez les liens suivants dans votre balise head pour charger directement les CSS:</p>
    
    <code>&lt;link href="<?php echo $base_url; ?>/public/assets/css/dashboard.css?v=<?php echo time(); ?>" rel="stylesheet"&gt;</code>
    <code>&lt;link href="<?php echo $base_url; ?>/public/assets/css/search.css?v=<?php echo time(); ?>" rel="stylesheet"&gt;</code>
    
    <div class="css-display">
        <h3>Exemple de styles avec la classe dashboard-pro</h3>
        <div class="dashboard-pro" style="padding: 20px; background-color: #f8f9fa; border-radius: 10px; border-left: 4px solid #4361ee;">
            <h4>Si les styles CSS sont correctement chargés, cette section devrait avoir une bordure bleue à gauche</h4>
            <p>Voici un exemple de contenu stylisé.</p>
        </div>
    </div>
    
    <h2>Liens utiles</h2>
    <ul>
        <li><a href="<?php echo $base_url; ?>/dashboard">Retour au tableau de bord</a></li>
        <li><a href="<?php echo $base_url; ?>">Page d'accueil</a></li>
    </ul>
</body>
</html> 
<?php
// Fichier de test pour vérifier la prise en compte des modifications

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test de mise à jour</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .warning {
            color: orange;
            font-weight: bold;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Test de mise à jour du site</h1>
        <p>Cette page vérifie si les modifications sont bien prises en compte par le serveur.</p>
        
        <h2>Informations sur le serveur :</h2>
        <ul>
            <li>Date et heure : <span class='success'>" . date('Y-m-d H:i:s') . "</span></li>
            <li>Version PHP : <span class='success'>" . phpversion() . "</span></li>
            <li>Serveur Web : <span class='success'>" . $_SERVER['SERVER_SOFTWARE'] . "</span></li>
        </ul>

        <h2>Vérification des fichiers modifiés :</h2>
        <p>Fichier index.php des agents :</p>";

$file_path = 'app/views/agents/index.php';
if (file_exists($file_path)) {
    $file_content = file_get_contents($file_path);
    $file_size = filesize($file_path);
    $file_modified = date("Y-m-d H:i:s", filemtime($file_path));
    
    echo "<ul>
            <li>Taille du fichier : <span class='success'>{$file_size} octets</span></li>
            <li>Dernière modification : <span class='success'>{$file_modified}</span></li>
            <li>Contient la colonne Matricule & Rang : <span class='" . 
                (strpos($file_content, 'Matricule & Rang') !== false ? 'success' : 'warning') . 
                "'>" . (strpos($file_content, 'Matricule & Rang') !== false ? 'Oui' : 'Non') . "</span></li>
            <li>Contient les styles CSS pour le rang : <span class='" . 
                (strpos($file_content, '.matricule-rang') !== false ? 'success' : 'warning') . 
                "'>" . (strpos($file_content, '.matricule-rang') !== false ? 'Oui' : 'Non') . "</span></li>
          </ul>";

} else {
    echo "<p class='warning'>Le fichier n'existe pas !</p>";
}

echo "<h2>Vérification du contrôleur :</h2>";
$controller_path = 'controllers/AgentController.php';
if (file_exists($controller_path)) {
    $controller_content = file_get_contents($controller_path);
    $controller_size = filesize($controller_path);
    $controller_modified = date("Y-m-d H:i:s", filemtime($controller_path));
    
    echo "<ul>
            <li>Taille du fichier : <span class='success'>{$controller_size} octets</span></li>
            <li>Dernière modification : <span class='success'>{$controller_modified}</span></li>
            <li>Contient le calcul du score : <span class='" . 
                (strpos($controller_content, 'niveau_scolaire') !== false && strpos($controller_content, 'score') !== false ? 'success' : 'warning') . 
                "'>" . (strpos($controller_content, 'niveau_scolaire') !== false && strpos($controller_content, 'score') !== false ? 'Oui' : 'Non') . "</span></li>
            <li>Contient l'attribution du rang : <span class='" . 
                (strpos($controller_content, '$agent->rang = $rang++') !== false ? 'success' : 'warning') . 
                "'>" . (strpos($controller_content, '$agent->rang = $rang++') !== false ? 'Oui' : 'Non') . "</span></li>
          </ul>";

} else {
    echo "<p class='warning'>Le fichier contrôleur n'existe pas !</p>";
}

echo "<h2>Solutions possibles :</h2>
<ol>
    <li>Vider le cache du navigateur (Ctrl+F5 ou Cmd+Shift+R)</li>
    <li>Vérifier les chemins des fichiers (ils doivent être dans app/views/agents/index.php et controllers/AgentController.php)</li>
    <li>Redémarrer le serveur Apache</li>
    <li>Vérifier les permissions des fichiers</li>
</ol>

<p>Pour revenir à la page des agents, <a href='/agentextra/agents/'>cliquez ici</a>.</p>

</div>
</body>
</html>"; 
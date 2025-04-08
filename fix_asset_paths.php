<?php
// Script pour vérifier et corriger les problèmes de chemins d'accès aux ressources

echo "<!DOCTYPE html>
<html>
<head>
    <title>Vérification des ressources</title>
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
        .error {
            color: red;
            font-weight: bold;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .fixed {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Vérification et correction des ressources</h1>
        <p>Cet outil vérifie les problèmes courants liés aux ressources statiques et aux bibliothèques JavaScript.</p>";

// 1. Vérification de l'existence du fichier logo
$logo_path = 'public/assets/img/logo.png';
echo "<h2>1. Vérification du logo</h2>";
if (file_exists($logo_path)) {
    echo "<p class='success'>✅ Le logo existe à l'emplacement : {$logo_path}</p>";
    echo "<p><img src='/{$logo_path}' alt='Logo' style='max-width: 150px;'></p>";
} else {
    echo "<p class='error'>❌ Le logo n'existe pas à l'emplacement attendu : {$logo_path}</p>";
    
    // Recherche du logo
    $search_paths = [
        'public/assets/images/logo.png',
        'assets/img/logo.png',
        'assets/images/logo.png',
        'public/img/logo.png'
    ];
    
    $found = false;
    foreach ($search_paths as $path) {
        if (file_exists($path)) {
            echo "<p class='success'>Logo trouvé à : {$path}</p>";
            echo "<p><img src='/{$path}' alt='Logo' style='max-width: 150px;'></p>";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "<p class='warning'>Logo introuvable dans les emplacements courants.</p>";
    }
}

// 2. Vérification des bibliothèques jQuery et DataTables
echo "<h2>2. Vérification des bibliothèques JavaScript</h2>";

$header_path = 'app/views/layouts/header.php';
$jquery_check = false;
$datatables_check = false;

if (file_exists($header_path)) {
    $header_content = file_get_contents($header_path);
    
    // Vérifier jQuery
    if (strpos($header_content, 'jquery') !== false) {
        echo "<p class='success'>✅ jQuery est inclus dans le header</p>";
        $jquery_check = true;
    } else {
        echo "<p class='error'>❌ jQuery n'est pas inclus dans le header</p>";
    }
    
    // Vérifier DataTables
    if (strpos($header_content, 'dataTables') !== false) {
        echo "<p class='success'>✅ DataTables est inclus dans le header</p>";
        $datatables_check = true;
    } else {
        echo "<p class='error'>❌ DataTables n'est pas inclus dans le header</p>";
    }
} else {
    echo "<p class='error'>❌ Fichier header.php introuvable</p>";
}

// 3. Correction automatique des chemins dans les fichiers agents
echo "<h2>3. Vérification des chemins dans les fichiers agents</h2>";

$agent_files = [
    'agents/index.php',
    'agents/create.php',
    'agents/edit.php',
    'agents/view.php'
];

$fixed_count = 0;

foreach ($agent_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Vérifier les chemins d'image incorrects
        if (strpos($content, '../assets/images/logo.png') !== false) {
            $new_content = str_replace(
                '../assets/images/logo.png', 
                '/agentextra/public/assets/img/logo.png', 
                $content
            );
            
            file_put_contents($file, $new_content);
            echo "<p class='fixed'>✅ Chemin du logo corrigé dans {$file}</p>";
            $fixed_count++;
        } else {
            echo "<p class='success'>✅ Aucun problème de chemin dans {$file}</p>";
        }
    } else {
        echo "<p class='warning'>⚠️ Fichier {$file} introuvable</p>";
    }
}

// 4. Suggestions pour les corrections manuelles
echo "<h2>4. Actions recommandées</h2>";
echo "<ol>";

if (!$jquery_check) {
    echo "<li class='warning'>Ajoutez jQuery dans le header avant les autres scripts :
    <pre>&lt;script src=\"https://code.jquery.com/jquery-3.6.0.min.js\"&gt;&lt;/script&gt;</pre></li>";
}

if (!$datatables_check) {
    echo "<li class='warning'>Ajoutez DataTables après jQuery :
    <pre>&lt;link href=\"https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css\" rel=\"stylesheet\"&gt;
&lt;script src=\"https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js\"&gt;&lt;/script&gt;
&lt;script src=\"https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js\"&gt;&lt;/script&gt;</pre></li>";
}

echo "<li>Initialisez DataTables dans vos pages :
<pre>$(document).ready(function() {
    $('#dataTable').DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json'
        }
    });
});</pre></li>";

echo "</ol>";

echo "<h2>Résumé</h2>";
echo "<p>{$fixed_count} fichiers ont été corrigés automatiquement.</p>";
echo "<p>Pour voir les changements, <a href='/agentextra/agents/'>rechargez la page des agents</a> (CTRL+F5).</p>";

echo "</div>
</body>
</html>"; 
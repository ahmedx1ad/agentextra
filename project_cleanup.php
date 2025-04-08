<?php
/**
 * Script de nettoyage et d'optimisation d'AgentExtra
 * Ce script va:
 * 1. Analyser le projet pour trouver les fichiers inutiles et doublons
 * 2. Optimiser les performances
 * 3. Corriger les erreurs courantes
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Démarrer le chronomètre
$start_time = microtime(true);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Nettoyage du Projet AgentExtra</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #2c3e50; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .box { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
        button, .button { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .file-list { max-height: 300px; overflow-y: auto; }
        .action-btn { padding: 3px 10px; margin-right: 5px; font-size: 0.8em; }
        .delete-btn { background-color: #e74c3c; }
        .keep-btn { background-color: #2ecc71; }
        .actions { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Nettoyage et Optimisation du Projet AgentExtra</h1>
    <p>Ce script analyse votre projet pour identifier et corriger les problèmes suivants:</p>
    <ul>
        <li>Fichiers de diagnostic et correctifs temporaires</li>
        <li>Fichiers dupliqués ou redondants</li>
        <li>Scripts de test et débogage</li>
        <li>Problèmes de configuration qui affectent les performances</li>
        <li>Correction des erreurs communes</li>
    </ul>
";

// Fonction pour afficher le statut
function status($message, $type = 'info') {
    $icon = $type === 'success' ? '✓' : ($type === 'error' ? '✗' : 'ℹ');
    echo "<p class='{$type}'>{$icon} {$message}</p>";
}

// Fonction pour formater la taille de fichier
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// Fonction pour vérifier si un fichier est probablement temporaire
function isTemporaryFile($filename) {
    $patterns = [
        '/^fix_.*\.php$/',
        '/^test_.*\.php$/',
        '/^debug_.*\.php$/',
        '/^.*_fix\.php$/',
        '/^.*_bak\..*$/',
        '/^.*\.bak$/',
        '/^.*\.tmp$/',
        '/^.*\.old$/',
        '/^.*\.backup$/',
        '/^language_fix\.php$/',
        '/^fix_language_settings\.php$/',
        '/^change_lang\.php$/',
        '/^restart\.php$/',
        '/^reset_settings\.php$/',
        '/^debug_settings\.php$/',
        '/^fix_settings_advanced\.php$/',
        '/^fix_settings\.php$/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, basename($filename))) {
            return true;
        }
    }
    
    return false;
}

// Fonction pour trouver des fichiers en double
function findDuplicateFiles($dir, $ignoreList = []) {
    $fileHashes = [];
    $duplicates = [];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $path = $file->getPathname();
            
            // Ignorer certains fichiers
            foreach ($ignoreList as $ignore) {
                if (strpos($path, $ignore) !== false) {
                    continue 2;
                }
            }
            
            $hash = md5_file($path);
            $size = $file->getSize();
            
            // Ignorer les fichiers très petits (< 100 octets)
            if ($size < 100) {
                continue;
            }
            
            if (!isset($fileHashes[$hash])) {
                $fileHashes[$hash] = [$path];
            } else {
                $fileHashes[$hash][] = $path;
                $duplicates[$hash] = $fileHashes[$hash];
            }
        }
    }
    
    return $duplicates;
}

// Fonction récursive pour supprimer un dossier et son contenu
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . DIRECTORY_SEPARATOR . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir($dir);
    }
}

// Obtenir le chemin absolu du dossier du projet
$projectDir = dirname(__FILE__);

// 1. Analyser les fichiers temporaires et de diagnostic
echo "<h2>1. Identification des fichiers temporaires et de diagnostic</h2>";

$tempFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && isTemporaryFile($file->getFilename())) {
        $tempFiles[] = [
            'path' => $file->getPathname(),
            'size' => $file->getSize(),
            'modified' => $file->getMTime()
        ];
    }
}

// Trier par date de modification (plus récent en premier)
usort($tempFiles, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

if (count($tempFiles) > 0) {
    echo "<p>Les fichiers temporaires suivants ont été identifiés:</p>";
    echo "<div class='box file-list'>";
    echo "<form method='post' id='temp-files-form'>";
    echo "<table>";
    echo "<tr><th>Fichier</th><th>Taille</th><th>Dernière modification</th><th>Actions</th></tr>";
    
    foreach ($tempFiles as $index => $file) {
        $relativePath = str_replace($projectDir . DIRECTORY_SEPARATOR, '', $file['path']);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($relativePath) . "</td>";
        echo "<td>" . formatSize($file['size']) . "</td>";
        echo "<td>" . date("Y-m-d H:i:s", $file['modified']) . "</td>";
        echo "<td class='actions'>";
        echo "<input type='checkbox' name='delete_temp[]' value='" . htmlspecialchars($file['path']) . "' checked> Supprimer";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<button type='submit' name='action' value='delete_temp' class='button delete-btn'>Supprimer les fichiers sélectionnés</button>";
    echo "<button type='button' onclick='toggleCheckboxes(\"delete_temp[]\")' class='button'>Tout sélectionner/désélectionner</button>";
    echo "</form>";
    echo "</div>";
} else {
    status("Aucun fichier temporaire identifié.", 'success');
}

// 2. Rechercher les fichiers dupliqués
echo "<h2>2. Identification des fichiers dupliqués</h2>";

$ignoreList = ['/vendor/', '/node_modules/', '/public/assets/'];
$duplicates = findDuplicateFiles($projectDir, $ignoreList);

if (count($duplicates) > 0) {
    echo "<p>Les groupes de fichiers suivants semblent être identiques:</p>";
    echo "<div class='box file-list'>";
    echo "<form method='post' id='duplicate-files-form'>";
    
    $dupIndex = 0;
    foreach ($duplicates as $hash => $files) {
        $dupIndex++;
        echo "<h3>Groupe " . $dupIndex . " - " . count($files) . " fichiers</h3>";
        echo "<table>";
        echo "<tr><th>Fichier</th><th>Taille</th><th>Dernière modification</th><th>Actions</th></tr>";
        
        // Trier les fichiers pour que les plus anciens soient en premier (plus susceptibles d'être supprimés)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        foreach ($files as $index => $file) {
            $relativePath = str_replace($projectDir . DIRECTORY_SEPARATOR, '', $file);
            $fileObj = new SplFileInfo($file);
            
            echo "<tr>";
            echo "<td>" . htmlspecialchars($relativePath) . "</td>";
            echo "<td>" . formatSize($fileObj->getSize()) . "</td>";
            echo "<td>" . date("Y-m-d H:i:s", $fileObj->getMTime()) . "</td>";
            echo "<td class='actions'>";
            // Premier fichier non coché (à conserver), les autres cochés (à supprimer)
            if ($index > 0) {
                echo "<input type='checkbox' name='delete_dup[]' value='" . htmlspecialchars($file) . "' checked> Supprimer";
            } else {
                echo "<span class='success'>✓ Conserver</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<button type='submit' name='action' value='delete_dup' class='button delete-btn'>Supprimer les doublons sélectionnés</button>";
    echo "</form>";
    echo "</div>";
} else {
    status("Aucun fichier en double identifié.", 'success');
}

// 3. Vérifier les répertoires vides qui peuvent être supprimés
echo "<h2>3. Identification des répertoires vides</h2>";

$emptyDirs = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::CHILD_FIRST
);

foreach ($iterator as $path) {
    if ($path->isDir()) {
        $dirPath = $path->getPathname();
        
        // Ignorer certains dossiers
        if (strpos($dirPath, '.git') !== false || 
            strpos($dirPath, 'vendor') !== false || 
            strpos($dirPath, 'node_modules') !== false) {
            continue;
        }
        
        $isDirEmpty = !(new \FilesystemIterator($dirPath))->valid();
        if ($isDirEmpty) {
            $emptyDirs[] = $dirPath;
        }
    }
}

if (count($emptyDirs) > 0) {
    echo "<p>Les répertoires vides suivants ont été identifiés:</p>";
    echo "<div class='box file-list'>";
    echo "<form method='post' id='empty-dirs-form'>";
    echo "<table>";
    echo "<tr><th>Répertoire</th><th>Actions</th></tr>";
    
    foreach ($emptyDirs as $dir) {
        $relativePath = str_replace($projectDir . DIRECTORY_SEPARATOR, '', $dir);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($relativePath) . "</td>";
        echo "<td class='actions'>";
        echo "<input type='checkbox' name='delete_dirs[]' value='" . htmlspecialchars($dir) . "' checked> Supprimer";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<button type='submit' name='action' value='delete_dirs' class='button delete-btn'>Supprimer les répertoires sélectionnés</button>";
    echo "<button type='button' onclick='toggleCheckboxes(\"delete_dirs[]\")' class='button'>Tout sélectionner/désélectionner</button>";
    echo "</form>";
    echo "</div>";
} else {
    status("Aucun répertoire vide identifié.", 'success');
}

// 4. Suggérer des améliorations de performances
echo "<h2>4. Suggestions d'optimisation</h2>";
echo "<div class='box'>";

// Vérifier la configuration PHP
echo "<h3>Configuration PHP</h3>";
echo "<table>";
echo "<tr><th>Paramètre</th><th>Valeur actuelle</th><th>Recommandation</th></tr>";

// OPCache
$opcache_enabled = function_exists('opcache_get_status') && opcache_get_status(false);
echo "<tr>";
echo "<td>OPCache</td>";
echo "<td>" . ($opcache_enabled ? "Activé" : "Désactivé") . "</td>";
echo "<td>" . ($opcache_enabled ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Recommandé d'activer OPCache pour de meilleures performances</span>") . "</td>";
echo "</tr>";

// Cache de session
$session_cache_limiter = ini_get('session.cache_limiter');
echo "<tr>";
echo "<td>Session Cache Limiter</td>";
echo "<td>" . $session_cache_limiter . "</td>";
echo "<td>" . ($session_cache_limiter == 'nocache' ? "<span class='warning'>⚠ Utiliser 'private' pour un meilleur équilibre entre performance et sécurité</span>" : "<span class='success'>✓ OK</span>") . "</td>";
echo "</tr>";

// Compression GZip
$gzip_enabled = ini_get('zlib.output_compression');
echo "<tr>";
echo "<td>Compression GZip</td>";
echo "<td>" . ($gzip_enabled ? "Activé" : "Désactivé") . "</td>";
echo "<td>" . ($gzip_enabled ? "<span class='success'>✓ OK</span>" : "<span class='warning'>⚠ Recommandé d'activer zlib.output_compression pour réduire la taille des réponses</span>") . "</td>";
echo "</tr>";

echo "</table>";

// Vérifier les fichiers CSS et JS (minification)
echo "<h3>Optimisation des ressources statiques</h3>";

$cssJsFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($projectDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['css', 'js'])) {
        $filePath = $file->getPathname();
        $fileSize = $file->getSize();
        $content = file_get_contents($filePath);
        
        // Vérifier si le fichier est déjà minifié
        $isMinified = (strpos($file->getFilename(), '.min.') !== false);
        
        // Heuristique simple pour déterminer si un fichier est minifié
        $linesCount = substr_count($content, "\n");
        $isShortLines = ($linesCount < 5 || ($linesCount / $fileSize < 0.01));
        
        $isPotentiallyMinifiable = (!$isMinified && !$isShortLines && $fileSize > 1024); // Plus de 1KB
        
        if ($isPotentiallyMinifiable) {
            $cssJsFiles[] = [
                'path' => $filePath,
                'type' => $file->getExtension(),
                'size' => $fileSize
            ];
        }
    }
}

if (count($cssJsFiles) > 0) {
    echo "<p>Les fichiers CSS/JS suivants pourraient être minifiés pour de meilleures performances:</p>";
    echo "<table>";
    echo "<tr><th>Fichier</th><th>Type</th><th>Taille</th></tr>";
    
    foreach ($cssJsFiles as $file) {
        $relativePath = str_replace($projectDir . DIRECTORY_SEPARATOR, '', $file['path']);
        echo "<tr>";
        echo "<td>" . htmlspecialchars($relativePath) . "</td>";
        echo "<td>" . strtoupper($file['type']) . "</td>";
        echo "<td>" . formatSize($file['size']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p class='warning'>⚠ Suggestion: Minifiez ces fichiers pour améliorer les temps de chargement.</p>";
} else {
    echo "<p class='success'>✓ Aucun fichier CSS/JS non-minifié trouvé.</p>";
}

// Vérifier la configuration de mise en cache des fichiers statiques
$htaccessPath = $projectDir . '/.htaccess';
$hasCacheHeaders = false;

if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    $hasCacheHeaders = strpos($htaccessContent, 'ExpiresActive') !== false || 
                     strpos($htaccessContent, 'Cache-Control') !== false;
}

if (!$hasCacheHeaders) {
    echo "<h3>Configuration de cache</h3>";
    echo "<p class='warning'>⚠ Aucune directive de mise en cache n'a été trouvée dans votre fichier .htaccess.</p>";
    echo "<p>Ajoutez les directives suivantes pour améliorer les performances:</p>";
    echo "<pre>&lt;IfModule mod_expires.c&gt;
    ExpiresActive On
    ExpiresByType image/jpg \"access plus 1 year\"
    ExpiresByType image/jpeg \"access plus 1 year\"
    ExpiresByType image/gif \"access plus 1 year\"
    ExpiresByType image/png \"access plus 1 year\"
    ExpiresByType image/svg+xml \"access plus 1 year\"
    ExpiresByType text/css \"access plus 1 month\"
    ExpiresByType application/pdf \"access plus 1 month\"
    ExpiresByType text/javascript \"access plus 1 month\"
    ExpiresByType application/javascript \"access plus 1 month\"
    ExpiresByType application/x-javascript \"access plus 1 month\"
    ExpiresByType application/x-shockwave-flash \"access plus 1 month\"
    ExpiresByType image/x-icon \"access plus 1 year\"
    ExpiresDefault \"access plus 2 days\"
&lt;/IfModule&gt;</pre>";
}

echo "</div>";

// 5. Corriger les erreurs courantes
echo "<h2>5. Correction des erreurs courantes</h2>";
echo "<div class='box'>";

// Problème de session cookie secure
$bootstrapFile = $projectDir . '/bootstrap.php';
$bootstrapFixed = false;

if (file_exists($bootstrapFile)) {
    $bootstrapContent = file_get_contents($bootstrapFile);
    
    if (strpos($bootstrapContent, "ini_set('session.cookie_secure', '1');") !== false) {
        echo "<h3>Problème de cookie de session</h3>";
        echo "<p class='warning'>⚠ La configuration <code>session.cookie_secure = 1</code> peut causer des problèmes sur un environnement local sans HTTPS.</p>";
        echo "<form method='post'>";
        echo "<input type='hidden' name='fix_cookie_secure' value='1'>";
        echo "<button type='submit' name='action' value='fix_cookie_secure' class='button'>Corriger ce problème</button>";
        echo "</form>";
    } else {
        $bootstrapFixed = true;
    }
} else {
    echo "<p class='error'>✗ Fichier bootstrap.php non trouvé.</p>";
}

// 6. Vérifier et améliorer la structure du projet
echo "<h3>Structure du projet</h3>";

// Vérifier si le fichier .gitignore existe et est correctement configuré
$gitignorePath = $projectDir . '/.gitignore';
$gitignoreNeeds = [
    '/vendor/',
    '/node_modules/',
    '/.env',
    '/config/config.local.php',
    '/cache/',
    '/logs/',
    '*.log',
    '*.cache',
    '*.tmp'
];

$gitignoreContent = file_exists($gitignorePath) ? file_get_contents($gitignorePath) : '';
$missingGitignore = [];

foreach ($gitignoreNeeds as $entry) {
    if (strpos($gitignoreContent, $entry) === false) {
        $missingGitignore[] = $entry;
    }
}

if (!empty($missingGitignore)) {
    echo "<p class='warning'>⚠ Votre fichier .gitignore pourrait être amélioré en ajoutant:</p>";
    echo "<pre>" . implode("\n", $missingGitignore) . "</pre>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='fix_gitignore' value='1'>";
    echo "<button type='submit' name='action' value='fix_gitignore' class='button'>Ajouter ces entrées</button>";
    echo "</form>";
} else if (!file_exists($gitignorePath)) {
    echo "<p class='warning'>⚠ Fichier .gitignore non trouvé. Il est recommandé d'en créer un.</p>";
    echo "<form method='post'>";
    echo "<input type='hidden' name='create_gitignore' value='1'>";
    echo "<button type='submit' name='action' value='create_gitignore' class='button'>Créer un .gitignore</button>";
    echo "</form>";
} else {
    echo "<p class='success'>✓ Fichier .gitignore correctement configuré.</p>";
}

echo "</div>";

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    
    echo "<h2>Résultats des actions</h2>";
    echo "<div class='box'>";
    
    switch ($action) {
        case 'delete_temp':
            if (isset($_POST['delete_temp']) && is_array($_POST['delete_temp'])) {
                $deleted = 0;
                $failed = 0;
                
                foreach ($_POST['delete_temp'] as $file) {
                    if (file_exists($file) && is_file($file)) {
                        if (unlink($file)) {
                            $deleted++;
                        } else {
                            $failed++;
                        }
                    }
                }
                
                if ($deleted > 0) {
                    status("{$deleted} fichiers temporaires supprimés avec succès.", 'success');
                }
                
                if ($failed > 0) {
                    status("Échec de la suppression de {$failed} fichiers.", 'error');
                }
            } else {
                status("Aucun fichier sélectionné pour la suppression.", 'warning');
            }
            break;
            
        case 'delete_dup':
            if (isset($_POST['delete_dup']) && is_array($_POST['delete_dup'])) {
                $deleted = 0;
                $failed = 0;
                
                foreach ($_POST['delete_dup'] as $file) {
                    if (file_exists($file) && is_file($file)) {
                        if (unlink($file)) {
                            $deleted++;
                        } else {
                            $failed++;
                        }
                    }
                }
                
                if ($deleted > 0) {
                    status("{$deleted} fichiers dupliqués supprimés avec succès.", 'success');
                }
                
                if ($failed > 0) {
                    status("Échec de la suppression de {$failed} fichiers.", 'error');
                }
            } else {
                status("Aucun fichier sélectionné pour la suppression.", 'warning');
            }
            break;
            
        case 'delete_dirs':
            if (isset($_POST['delete_dirs']) && is_array($_POST['delete_dirs'])) {
                $deleted = 0;
                $failed = 0;
                
                foreach ($_POST['delete_dirs'] as $dir) {
                    if (is_dir($dir)) {
                        try {
                            rmdir($dir);
                            $deleted++;
                        } catch (Exception $e) {
                            $failed++;
                        }
                    }
                }
                
                if ($deleted > 0) {
                    status("{$deleted} répertoires vides supprimés avec succès.", 'success');
                }
                
                if ($failed > 0) {
                    status("Échec de la suppression de {$failed} répertoires.", 'error');
                }
            } else {
                status("Aucun répertoire sélectionné pour la suppression.", 'warning');
            }
            break;
            
        case 'fix_cookie_secure':
            if (isset($_POST['fix_cookie_secure']) && file_exists($bootstrapFile)) {
                $bootstrapContent = file_get_contents($bootstrapFile);
                $newContent = str_replace(
                    "ini_set('session.cookie_secure', '1');", 
                    "ini_set('session.cookie_secure', '0'); // Modifié pour environnement local", 
                    $bootstrapContent
                );
                
                if (file_put_contents($bootstrapFile, $newContent)) {
                    status("Configuration des cookies de session corrigée pour environnement local.", 'success');
                    $bootstrapFixed = true;
                } else {
                    status("Impossible de modifier le fichier bootstrap.php. Vérifiez les permissions.", 'error');
                }
            }
            break;
            
        case 'fix_gitignore':
            if (isset($_POST['fix_gitignore'])) {
                if (file_exists($gitignorePath)) {
                    $content = file_get_contents($gitignorePath);
                    $content .= "\n\n# Added by cleanup script\n" . implode("\n", $missingGitignore);
                    
                    if (file_put_contents($gitignorePath, $content)) {
                        status("Fichier .gitignore mis à jour avec succès.", 'success');
                    } else {
                        status("Impossible de mettre à jour le fichier .gitignore.", 'error');
                    }
                }
            }
            break;
            
        case 'create_gitignore':
            if (isset($_POST['create_gitignore'])) {
                $content = "# Generated by cleanup script\n\n" . implode("\n", $gitignoreNeeds);
                
                if (file_put_contents($gitignorePath, $content)) {
                    status("Fichier .gitignore créé avec succès.", 'success');
                } else {
                    status("Impossible de créer le fichier .gitignore.", 'error');
                }
            }
            break;
    }
    
    echo "</div>";
}

// Calculer le temps d'exécution
$execution_time = microtime(true) - $start_time;

// Afficher un résumé
echo "<h2>Résumé</h2>";
echo "<div class='box'>";
echo "<ul>";
echo "<li>" . count($tempFiles) . " fichiers temporaires identifiés</li>";
echo "<li>" . count($duplicates) . " groupes de fichiers dupliqués</li>";
echo "<li>" . count($emptyDirs) . " répertoires vides</li>";
echo "<li>" . ($bootstrapFixed ? "Configuration des cookies de session : OK" : "Configuration des cookies de session : À corriger") . "</li>";
echo "</ul>";
echo "<p>Temps d'exécution : " . round($execution_time, 2) . " secondes</p>";
echo "</div>";

// Ajouter JavaScript pour la gestion des checkboxes
echo "<script>
function toggleCheckboxes(name) {
    var checkboxes = document.getElementsByName(name);
    var allChecked = true;
    
    for (var i = 0; i < checkboxes.length; i++) {
        if (!checkboxes[i].checked) {
            allChecked = false;
            break;
        }
    }
    
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = !allChecked;
    }
}
</script>";

echo "</body>
</html>"; 
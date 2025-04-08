<?php
/**
 * Script de nettoyage automatique des fichiers temporaires
 * À exécuter via une tâche cron (par exemple: 0 0 * * * php /chemin/vers/cleanup_temp.php)
 */

// Activer l'affichage des erreurs en mode débogage
define('DEBUG', false);
if (DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Obtenir le chemin de base du projet
$baseDir = __DIR__;

// Fichier de log pour le nettoyage
$logFile = $baseDir . '/cleanup_log.txt';

/**
 * Journaliser un message
 */
function log_message($message) {
    global $logFile;
    $date = date('Y-m-d H:i:s');
    $logMessage = "[{$date}] {$message}" . PHP_EOL;
    
    if (DEBUG) {
        echo $logMessage;
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Nettoyer les fichiers temporaires plus anciens que X jours
 */
function cleanup_temp_files($days = 7, $extensions = []) {
    global $baseDir;
    
    // Si aucune extension spécifiée, utiliser les valeurs par défaut
    if (empty($extensions)) {
        $extensions = ['log', 'tmp', 'temp', 'bak', 'old', 'backup'];
    }
    
    $count = 0;
    $totalSize = 0;
    
    // Calculer la date limite
    $limitTime = time() - ($days * 86400);
    
    log_message("Début du nettoyage des fichiers temporaires plus anciens que {$days} jours");
    
    // Parcourir récursivement le répertoire
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $fileInfo) {
        // Ignorer les répertoires
        if ($fileInfo->isDir()) {
            continue;
        }
        
        $filePath = $fileInfo->getPathname();
        $fileExt = strtolower($fileInfo->getExtension());
        
        // Vérifier si le fichier correspond aux critères
        if (in_array($fileExt, $extensions) && $fileInfo->getMTime() < $limitTime) {
            $fileSize = $fileInfo->getSize();
            log_message("Suppression du fichier: {$filePath} ({$fileSize} octets)");
            
            if (unlink($filePath)) {
                $count++;
                $totalSize += $fileSize;
            } else {
                log_message("ERREUR: Impossible de supprimer {$filePath}");
            }
        }
    }
    
    log_message("Fin du nettoyage. {$count} fichiers supprimés ({$totalSize} octets)");
    return [$count, $totalSize];
}

/**
 * Nettoyer les fichiers de log volumineux
 */
function cleanup_large_logs($maxSize = 10485760) {  // 10 MB par défaut
    global $baseDir;
    
    $count = 0;
    $totalSize = 0;
    
    log_message("Début du nettoyage des fichiers de log volumineux (> " . formatSize($maxSize) . ")");
    
    // Répertoire des logs
    $logDirs = [
        $baseDir . '/logs',
        $baseDir . '/log',
        $baseDir . '/app/logs'
    ];
    
    foreach ($logDirs as $logDir) {
        if (!is_dir($logDir)) {
            continue;
        }
        
        $files = glob($logDir . '/*.log');
        
        foreach ($files as $file) {
            $fileSize = filesize($file);
            
            if ($fileSize > $maxSize) {
                log_message("Traitement du fichier log: {$file} (" . formatSize($fileSize) . ")");
                
                // Conserver les 1000 dernières lignes seulement
                $tempFile = $file . '.tmp';
                exec("tail -n 1000 \"{$file}\" > \"{$tempFile}\"");
                
                if (file_exists($tempFile)) {
                    $newSize = filesize($tempFile);
                    
                    // Renommer le fichier temporaire
                    if (rename($tempFile, $file)) {
                        $count++;
                        $totalSize += ($fileSize - $newSize);
                        log_message("Fichier réduit de " . formatSize($fileSize) . " à " . formatSize($newSize));
                    } else {
                        log_message("ERREUR: Impossible de remplacer {$file}");
                        unlink($tempFile);
                    }
                }
            }
        }
    }
    
    log_message("Fin du nettoyage des logs. {$count} fichiers réduits (" . formatSize($totalSize) . " économisés)");
    return [$count, $totalSize];
}

/**
 * Nettoyer les répertoires temporaires
 */
function cleanup_temp_dirs() {
    global $baseDir;
    
    $tempDirs = [
        $baseDir . '/tmp',
        $baseDir . '/temp',
        $baseDir . '/cache'
    ];
    
    $filesDeleted = 0;
    
    log_message("Début du nettoyage des répertoires temporaires");
    
    foreach ($tempDirs as $dir) {
        if (is_dir($dir)) {
            log_message("Nettoyage du répertoire: {$dir}");
            $filesDeleted += delete_directory_contents($dir);
        }
    }
    
    log_message("Fin du nettoyage des répertoires temporaires. {$filesDeleted} fichiers supprimés");
    return $filesDeleted;
}

/**
 * Supprimer le contenu d'un répertoire sans supprimer le répertoire lui-même
 */
function delete_directory_contents($dir) {
    $count = 0;
    
    if (is_dir($dir)) {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $filePath = $dir . '/' . $file;
                
                if (is_dir($filePath)) {
                    $count += delete_directory_contents($filePath);
                    
                    // Supprimer le répertoire s'il est vide
                    if (count(scandir($filePath)) == 2) {  // Seulement . et ..
                        rmdir($filePath);
                        $count++;
                    }
                } else {
                    if (unlink($filePath)) {
                        $count++;
                    }
                }
            }
        }
    }
    
    return $count;
}

/**
 * Formater la taille en unités lisibles (Ko, Mo, Go)
 */
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

// -------------- EXÉCUTION PRINCIPALE --------------

log_message("===== DÉBUT DU SCRIPT DE NETTOYAGE =====");

// 1. Nettoyage des fichiers temporaires
list($tempCount, $tempSize) = cleanup_temp_files(7);

// 2. Nettoyage des gros fichiers de log
list($logCount, $logSize) = cleanup_large_logs();

// 3. Nettoyage des répertoires temporaires
$dirCount = cleanup_temp_dirs();

// Récapitulatif
log_message("===== RÉCAPITULATIF =====");
log_message("Fichiers temporaires supprimés: {$tempCount} (" . formatSize($tempSize) . ")");
log_message("Fichiers de log réduits: {$logCount} (" . formatSize($logSize) . " économisés)");
log_message("Fichiers supprimés dans les répertoires temporaires: {$dirCount}");
log_message("===== FIN DU SCRIPT DE NETTOYAGE ====="); 
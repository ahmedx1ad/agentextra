<?php
// Script d'importation des données vers la base de données unifiée
require_once __DIR__ . '/bootstrap.php';
use app\Config\DB;

// Fonction d'affichage des messages
function logMessage($message) {
    echo date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
}

// Fonction pour exécuter une requête SQL avec gestion des erreurs
function executeQuery($sql, $params = []) {
    try {
        $result = DB::query($sql, $params);
        return $result;
    } catch (Exception $e) {
        logMessage("ERREUR SQL: " . $e->getMessage());
        logMessage("Requête: " . $sql);
        logMessage("Paramètres: " . print_r($params, true));
        return false;
    }
}

// Commencer la migration
logMessage("=== DÉBUT DE LA MIGRATION DES DONNÉES VERS LA BASE DE DONNÉES UNIFIÉE ===");

try {
    // Vérifier si la base de données existe
    $db = DB::getInstance();
    logMessage("Connexion à la base de données établie avec succès.");

    // Initialiser les compteurs
    $importedUsers = 0;
    $importedServices = 0;
    $importedResponsables = 0;
    $importedAgents = 0;
    $importedPerformances = 0;
    $importedFavorites = 0;

    // 1. Migration des utilisateurs (si vous avez des utilisateurs dans une autre base)
    logMessage("Migration des utilisateurs...");
    // Cette partie est à adapter en fonction de vos anciennes tables
    // Exemple: 
    // $oldUsers = executeQuery("SELECT * FROM old_users_table");
    // while ($user = $oldUsers->fetch()) {
    //     $sql = "INSERT INTO users (username, email, password, role, full_name, active) 
    //             VALUES (?, ?, ?, ?, ?, ?) 
    //             ON DUPLICATE KEY UPDATE email=VALUES(email)";
    //     executeQuery($sql, [$user->username, $user->email, $user->password, $user->role, $user->full_name, $user->active]);
    //     $importedUsers++;
    // }
    
    // 2. Migration des services
    logMessage("Migration des services...");
    // Exemple:
    // $oldServices = executeQuery("SELECT * FROM old_services_table");
    // while ($service = $oldServices->fetch()) {
    //     $sql = "INSERT INTO services (id, nom, description, active) 
    //             VALUES (?, ?, ?, ?) 
    //             ON DUPLICATE KEY UPDATE nom=VALUES(nom), description=VALUES(description)";
    //     executeQuery($sql, [$service->id, $service->nom, $service->description, $service->active ?? 1]);
    //     $importedServices++;
    // }
    
    // 3. Migration des responsables
    logMessage("Migration des responsables...");
    
    // 4. Migration des agents
    logMessage("Migration des agents...");
    
    // 5. Migration des performances
    logMessage("Migration des performances...");
    
    // 6. Migration des favoris
    logMessage("Migration des favoris utilisateur...");
    
    // Résumé de la migration
    logMessage("=== RÉSUMÉ DE LA MIGRATION ===");
    logMessage("Utilisateurs importés: $importedUsers");
    logMessage("Services importés: $importedServices");
    logMessage("Responsables importés: $importedResponsables");
    logMessage("Agents importés: $importedAgents");
    logMessage("Performances importées: $importedPerformances");
    logMessage("Favoris importés: $importedFavorites");
    logMessage("=== MIGRATION TERMINÉE AVEC SUCCÈS ===");
    
} catch (Exception $e) {
    logMessage("ERREUR: " . $e->getMessage());
    logMessage("Trace: " . $e->getTraceAsString());
    logMessage("=== MIGRATION TERMINÉE AVEC ERREURS ===");
    exit(1);
}

// Importation des données CSV
logMessage("=== IMPORTATION DES MODÈLES CSV ===");

// Fonction pour importer un fichier CSV dans une table
function importCsv($csvFile, $tableName, $mapping) {
    if (!file_exists($csvFile)) {
        logMessage("Le fichier $csvFile n'existe pas");
        return 0;
    }
    
    $file = fopen($csvFile, 'r');
    if (!$file) {
        logMessage("Impossible d'ouvrir le fichier $csvFile");
        return 0;
    }
    
    $headers = fgetcsv($file, 0, ',');
    if (!$headers) {
        logMessage("Impossible de lire les en-têtes du fichier $csvFile");
        fclose($file);
        return 0;
    }
    
    $count = 0;
    while (($data = fgetcsv($file, 0, ',')) !== FALSE) {
        $rowData = [];
        
        foreach ($mapping as $dbColumn => $csvIndex) {
            if (is_numeric($csvIndex) && isset($data[$csvIndex])) {
                $rowData[$dbColumn] = $data[$csvIndex];
            } elseif (is_string($csvIndex) && in_array($csvIndex, $headers)) {
                $index = array_search($csvIndex, $headers);
                if ($index !== false && isset($data[$index])) {
                    $rowData[$dbColumn] = $data[$index];
                }
            }
        }
        
        // Construire la requête d'insertion
        $columns = implode(', ', array_keys($rowData));
        $placeholders = implode(', ', array_fill(0, count($rowData), '?'));
        
        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
        if (executeQuery($sql, array_values($rowData))) {
            $count++;
        }
    }
    
    fclose($file);
    return $count;
}

// Importer les agents depuis le modèle CSV
if (file_exists('modele_import_agents.csv')) {
    logMessage("Importation des agents depuis le modèle CSV...");
    $mapping = [
        'matricule' => 0,
        'nom' => 1,
        'prenom' => 2,
        'date_naissance' => 3,
        'adresse' => 4,
        'telephone' => 5,
        'email' => 6,
        'service_id' => 7,
        'responsable_id' => 8,
        'statut' => 9,
        'niveau_scolaire' => 10,
        'nombre_experience' => 11,
        'taille' => 12,
        'poids' => 13,
        'permis' => 14
    ];
    
    $importedFromCsv = importCsv('modele_import_agents.csv', 'agents', $mapping);
    logMessage("$importedFromCsv agents importés depuis le CSV");
}

// Importer les responsables depuis le modèle CSV
if (file_exists('modele_import_responsables.csv')) {
    logMessage("Importation des responsables depuis le modèle CSV...");
    $mapping = [
        'matricule' => 0,
        'nom' => 1,
        'prenom' => 2,
        'email' => 3,
        'telephone' => 4,
        'service_id' => 5,
        'poste' => 6
    ];
    
    $importedFromCsv = importCsv('modele_import_responsables.csv', 'responsables', $mapping);
    logMessage("$importedFromCsv responsables importés depuis le CSV");
}

logMessage("=== IMPORTATION DES MODÈLES CSV TERMINÉE ===");
?> 
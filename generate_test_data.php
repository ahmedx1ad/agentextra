<?php
/**
 * Script de génération de données de test pour AgentExtra
 * 
 * Ce script génère:
 * - 10 services
 * - 20 responsables
 * - 50 agents
 * 
 * Exécutez ce script une seule fois pour remplir votre base de données
 * avec des données de test.
 */

// Charger les fichiers nécessaires
require_once __DIR__ . '/app/Config/config.php';
require_once __DIR__ . '/app/Config/DB.php';

// Définir le chemin d'inclusion automatique des classes
spl_autoload_register(function ($class) {
    $file = str_replace('\\', '/', $class) . '.php';
    if (file_exists(__DIR__ . '/' . $file)) {
        require_once __DIR__ . '/' . $file;
        return true;
    }
    return false;
});

// Fonction pour générer une date aléatoire dans une plage donnée
function randomDate($start, $end) {
    $timestamp = mt_rand(strtotime($start), strtotime($end));
    return date('Y-m-d', $timestamp);
}

// Fonction pour générer un téléphone aléatoire
function randomPhone() {
    return '0' . rand(6, 7) . rand(0, 9) . str_repeat(rand(0, 9), 8);
}

// Fonction pour générer un email à partir d'un nom et prénom
function generateEmail($nom, $prenom) {
    $nom = strtolower(removeAccents($nom));
    $prenom = strtolower(removeAccents($prenom));
    return $prenom . '.' . $nom . '@example.com';
}

// Fonction pour enlever les accents d'une chaîne
function removeAccents($string) {
    $string = str_replace(
        ['à', 'á', 'â', 'ã', 'ä', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Û', 'Ü', 'Ý'],
        ['a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y'],
        $string
    );
    
    // Remplacer les espaces par des tirets bas
    return preg_replace('/\s+/', '_', $string);
}

// Connexion à la base de données
$db = \app\Config\DB::getInstance();

// Vérifier si des données existent déjà
$count = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
if ($count > 0) {
    echo "Des données existent déjà dans la table 'services'.\n";
    echo "Voulez-vous continuer et ajouter plus de données ? (o/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    if (strtolower($line) !== 'o') {
        echo "Opération annulée.\n";
        exit;
    }
}

// Tableaux de données pour la génération aléatoire
$noms = [
    'Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Dubois', 'Moreau', 'Laurent',
    'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard',
    'Bonnet', 'Dupont', 'Lambert', 'Fontaine', 'Rousseau', 'Vincent', 'Muller', 'Lefevre', 'Faure', 'Andre',
    'Mercier', 'Blanc', 'Guerin', 'Boyer', 'Garnier', 'Chevalier', 'Francois', 'Legrand', 'Gauthier', 'Garcia'
];

$prenoms = [
    'Jean', 'Pierre', 'Michel', 'André', 'Philippe', 'René', 'Louis', 'Alain', 'Jacques', 'Bernard',
    'Marie', 'Jeanne', 'Françoise', 'Monique', 'Catherine', 'Nathalie', 'Isabelle', 'Sylvie', 'Anne', 'Sophie',
    'Thomas', 'Nicolas', 'Julien', 'Sébastien', 'Christophe', 'David', 'Stéphane', 'Alexandre', 'Frédéric', 'Jérôme',
    'Julie', 'Camille', 'Laura', 'Émilie', 'Sarah', 'Aurélie', 'Claire', 'Céline', 'Pauline', 'Mathilde'
];

$villes = [
    'Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille',
    'Rennes', 'Reims', 'Le Havre', 'Saint-Étienne', 'Toulon', 'Grenoble', 'Dijon', 'Angers', 'Nîmes', 'Villeurbanne'
];

$niveaux_etudes = ['CAP/BEP', 'Baccalauréat', 'BTS/DUT', 'Licence', 'Master', 'Doctorat'];
$types_permis = ['A', 'B', 'C', 'D', 'BE', 'NONE'];
$langues = ['Français', 'Anglais', 'Espagnol', 'Allemand', 'Italien', 'Arabe', 'Chinois', 'Russe', 'Portugais', 'Japonais'];
$competences = ['Management', 'Finance', 'Marketing', 'Informatique', 'Ressources Humaines', 'Communication', 'Juridique', 'Commercial', 'Logistique', 'Technique'];

$services = [
    ['nom' => 'Direction Générale', 'description' => 'Pilotage stratégique de l\'entreprise'],
    ['nom' => 'Ressources Humaines', 'description' => 'Gestion des employés et du recrutement'],
    ['nom' => 'Comptabilité', 'description' => 'Gestion financière et comptable'],
    ['nom' => 'Marketing', 'description' => 'Stratégie marketing et communication'],
    ['nom' => 'Informatique', 'description' => 'Gestion des systèmes d\'information'],
    ['nom' => 'Commercial', 'description' => 'Vente et relation client'],
    ['nom' => 'Logistique', 'description' => 'Gestion des stocks et approvisionnements'],
    ['nom' => 'Production', 'description' => 'Fabrication et assemblage'],
    ['nom' => 'Recherche et Développement', 'description' => 'Innovation et conception de nouveaux produits'],
    ['nom' => 'Service Client', 'description' => 'Support et assistance aux clients']
];

// ===== ÉTAPE 1: CRÉATION DES SERVICES =====
echo "Création des services...\n";

$serviceIds = [];
try {
    foreach ($services as $service) {
        $sql = "INSERT INTO services (nom, description, created_at) VALUES (:nom, :description, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $service['nom'],
            ':description' => $service['description']
        ]);
        $serviceIds[] = $db->lastInsertId();
        echo "Service créé: " . $service['nom'] . "\n";
    }
} catch (Exception $e) {
    echo "Erreur lors de la création des services: " . $e->getMessage() . "\n";
    exit;
}

// ===== ÉTAPE 2: CRÉATION DES RESPONSABLES =====
echo "\nCréation des responsables...\n";

$responsableIds = [];
try {
    for ($i = 0; $i < 20; $i++) {
        $nom = $noms[array_rand($noms)];
        $prenom = $prenoms[array_rand($prenoms)];
        $email = generateEmail($nom, $prenom);
        $telephone = randomPhone();
        $ville = $villes[array_rand($villes)];
        $matricule = 'R' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
        $service_id = $serviceIds[array_rand($serviceIds)];
        $cin = 'CIN' . rand(100000, 999999);
        
        $sql = "INSERT INTO responsables (nom, prenom, email, telephone, service_id, ville, matricule, cin, created_at) 
                VALUES (:nom, :prenom, :email, :telephone, :service_id, :ville, :matricule, :cin, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':service_id' => $service_id,
            ':ville' => $ville,
            ':matricule' => $matricule,
            ':cin' => $cin
        ]);
        $responsableIds[] = $db->lastInsertId();
        echo "Responsable créé: $prenom $nom\n";
    }
} catch (Exception $e) {
    echo "Erreur lors de la création des responsables: " . $e->getMessage() . "\n";
    exit;
}

// ===== ÉTAPE 3: CRÉATION DES AGENTS =====
echo "\nCréation des agents...\n";

try {
    for ($i = 0; $i < 50; $i++) {
        $nom = $noms[array_rand($noms)];
        $prenom = $prenoms[array_rand($prenoms)];
        $email = generateEmail($nom, $prenom);
        $telephone = randomPhone();
        $adresse = rand(1, 99) . " rue " . $prenoms[array_rand($prenoms)];
        $ville = $villes[array_rand($villes)];
        $code_postal = rand(10000, 99999);
        $date_naissance = randomDate('1970-01-01', '2000-12-31');
        $date_embauche = randomDate('2010-01-01', date('Y-m-d'));
        $matricule = 'A' . str_pad($i + 1, 4, '0', STR_PAD_LEFT);
        $statut = (rand(0, 10) > 2) ? 'actif' : 'inactif'; // 80% actifs, 20% inactifs
        $service_id = $serviceIds[array_rand($serviceIds)];
        $responsable_id = $responsableIds[array_rand($responsableIds)];
        
        // Caractéristiques physiques
        $taille = rand(155, 200) / 100; // en mètres, entre 1.55 et 2.00
        $poids = rand(50, 100); // en kg
        
        // Formation et compétences
        $niveau_etudes = $niveaux_etudes[array_rand($niveaux_etudes)];
        $permis = $types_permis[array_rand($types_permis)];
        
        // Langues maîtrisées (entre 1 et 3 langues)
        $nb_langues = rand(1, 3);
        $langues_agent = [];
        for ($j = 0; $j < $nb_langues; $j++) {
            $langues_agent[] = $langues[array_rand($langues)];
        }
        $langues_maitrisees = implode(',', array_unique($langues_agent));
        
        // Compétences (entre 1 et 4 compétences)
        $nb_competences = rand(1, 4);
        $competences_agent = [];
        for ($j = 0; $j < $nb_competences; $j++) {
            $competences_agent[] = $competences[array_rand($competences)];
        }
        $competences_specifiques = implode(',', array_unique($competences_agent));
        
        // Expérience (entre 0 et 20 ans)
        $experience = rand(0, 20);
        
        // Calcul de la performance à partir des caractéristiques
        $performance = 0;
        // Niveau d'études (de 1 à 6 points)
        $valeur_diplome = array_search($niveau_etudes, $niveaux_etudes) + 1;
        $performance += $valeur_diplome * 0.5;
        
        // Expérience (0.3 point par année)
        $performance += $experience * 0.3;
        
        // Taille (bonus faible)
        $performance += $taille * 0.2;
        
        // Permis (1 point si a un permis autre que NONE)
        if ($permis != 'NONE') {
            $performance += 1;
        }
        
        // Langues (0.5 point par langue)
        $performance += count($langues_agent) * 0.5;
        
        // Arrondir la performance à 1 décimale
        $performance = round($performance, 1);
        
        $sql = "INSERT INTO agents (nom, prenom, email, telephone, adresse, ville, code_postal, date_naissance,
                date_embauche, matricule, statut, service_id, responsable_id, taille, poids,
                niveau_etudes, permis, langues_maitrisees, competences_specifiques, experience,
                performance, created_at)
                VALUES (:nom, :prenom, :email, :telephone, :adresse, :ville, :code_postal, :date_naissance,
                :date_embauche, :matricule, :statut, :service_id, :responsable_id, :taille, :poids,
                :niveau_etudes, :permis, :langues_maitrisees, :competences_specifiques, :experience,
                :performance, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':ville' => $ville,
            ':code_postal' => $code_postal,
            ':date_naissance' => $date_naissance,
            ':date_embauche' => $date_embauche,
            ':matricule' => $matricule,
            ':statut' => $statut,
            ':service_id' => $service_id,
            ':responsable_id' => $responsable_id,
            ':taille' => $taille,
            ':poids' => $poids,
            ':niveau_etudes' => $niveau_etudes,
            ':permis' => $permis,
            ':langues_maitrisees' => $langues_maitrisees,
            ':competences_specifiques' => $competences_specifiques,
            ':experience' => $experience,
            ':performance' => $performance
        ]);
        
        echo "Agent créé: $prenom $nom (Performance: $performance)\n";
    }
} catch (Exception $e) {
    echo "Erreur lors de la création des agents: " . $e->getMessage() . "\n";
    exit;
}

echo "\nGénération des données de test terminée avec succès !\n";
echo "10 services, 20 responsables et 50 agents ont été créés.\n";
echo "Vous pouvez maintenant tester l'application avec ces données.\n"; 
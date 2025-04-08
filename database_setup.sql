-- Script de création de la base de données unifiée pour l'application AgentExtra
-- Ce script centralise toutes les opérations SQL précédemment dispersées dans plusieurs fichiers

-- Création de la base de données si elle n'existe pas déjà
CREATE DATABASE IF NOT EXISTS agentextra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE agentextra;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'agent') NOT NULL DEFAULT 'agent',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des services
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB;

-- Table des responsables
CREATE TABLE IF NOT EXISTS responsables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    cin VARCHAR(20) DEFAULT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE,
    telephone VARCHAR(20),
    service_id INT,
    poste VARCHAR(100),
    date_debut DATE,
    photo_path VARCHAR(255),
    ville VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des agents
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    cin VARCHAR(20) DEFAULT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE,
    lieu_naissance VARCHAR(100),
    adresse TEXT,
    telephone VARCHAR(20),
    email VARCHAR(100),
    photo_path VARCHAR(255),
    service_id INT,
    responsable_id INT,
    date_recrutement DATE,
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    niveau_scolaire VARCHAR(50),
    nombre_experience INT DEFAULT 0,
    taille DECIMAL(3,2),
    poids INT,
    permis VARCHAR(10),
    langues_maitrisees VARCHAR(255),
    competences TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (responsable_id) REFERENCES responsables(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table agents_services pour la relation many-to-many
CREATE TABLE IF NOT EXISTS agents_services (
    agent_id INT NOT NULL,
    service_id INT NOT NULL,
    date_affectation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (agent_id, service_id),
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des performances
CREATE TABLE IF NOT EXISTS performances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    performance DECIMAL(4,2) NOT NULL DEFAULT 0,
    evaluation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    evaluateur_id INT,
    comments TEXT,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluateur_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des documents
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    path VARCHAR(255) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des favoris utilisateur
CREATE TABLE IF NOT EXISTS user_favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agent_id INT DEFAULT NULL,
    responsable_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE CASCADE,
    FOREIGN KEY (responsable_id) REFERENCES responsables(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, agent_id),
    UNIQUE KEY (user_id, responsable_id)
) ENGINE=InnoDB;

-- Insertion de données de base uniquement si les tables sont vides
-- Ajouter un utilisateur administrateur
INSERT INTO users (password_hash, role, first_name, last_name)
SELECT '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'admin', 'Admin', 'System'
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM users WHERE role = 'admin' LIMIT 1);

-- Ajouter un service par défaut
INSERT INTO services (nom, description)
SELECT 'Service Général', 'Service par défaut'
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM services LIMIT 1);

-- Index pour améliorer les performances des requêtes
-- Vérifier et supprimer les index existants avant de les recréer
DROP INDEX IF EXISTS idx_agents_service ON agents;
DROP INDEX IF EXISTS idx_agents_responsable ON agents;
DROP INDEX IF EXISTS idx_agents_statut ON agents;
DROP INDEX IF EXISTS idx_performances_agent ON performances;
DROP INDEX IF EXISTS idx_user_favorites ON user_favorites;

-- Création des index
ALTER TABLE agents ADD INDEX idx_agents_service (service_id);
ALTER TABLE agents ADD INDEX idx_agents_responsable (responsable_id);
ALTER TABLE agents ADD INDEX idx_agents_statut (statut);
ALTER TABLE performances ADD INDEX idx_performances_agent (agent_id);
ALTER TABLE user_favorites ADD INDEX idx_user_favorites (user_id, agent_id);

-- Ajout de la colonne ville dans la table responsables
ALTER TABLE responsables ADD COLUMN ville VARCHAR(100) DEFAULT NULL; 
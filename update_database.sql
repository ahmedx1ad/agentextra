-- Script de mise à jour de la base de données pour AgentExtra
-- Date: <?= date('Y-m-d') ?>

-- Vérifier et créer la table user_favorites si elle n'existe pas
CREATE TABLE IF NOT EXISTS `user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `responsable_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_responsable_unique` (`user_id`, `responsable_id`),
  KEY `fk_user_favorites_user_id` (`user_id`),
  KEY `fk_user_favorites_responsable_id` (`responsable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vérifier si la colonne photo existe dans la table responsables, sinon l'ajouter
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'responsables'
  AND COLUMN_NAME = 'photo'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `responsables` ADD COLUMN `photo` VARCHAR(255) NULL DEFAULT NULL AFTER `matricule`', 'SELECT "La colonne photo existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier si la colonne active existe dans la table responsables, sinon l'ajouter
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'responsables'
  AND COLUMN_NAME = 'active'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `responsables` ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `photo`', 'SELECT "La colonne active existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier si la colonne updated_at existe, sinon l'ajouter
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'responsables'
  AND COLUMN_NAME = 'updated_at'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `responsables` ADD COLUMN `updated_at` DATETIME NULL DEFAULT NULL AFTER `created_at`', 'SELECT "La colonne updated_at existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier si la colonne created_at existe, sinon l'ajouter
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'responsables'
  AND COLUMN_NAME = 'created_at'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `responsables` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', 'SELECT "La colonne created_at existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Mettre à jour les responsables existants pour définir updated_at si nécessaire
UPDATE `responsables` SET `updated_at` = `created_at` WHERE `updated_at` IS NULL AND `created_at` IS NOT NULL;

-- Création d'un dossier pour les photos des responsables s'il n'existe pas
-- Note: Cette commande ne peut pas être exécutée directement en SQL. Exécutez-la manuellement dans votre environnement.
-- mkdir -p uploads/responsables/photos

-- Ajout des contraintes de clé étrangère pour la table user_favorites
-- Note: Assurez-vous que les tables users et responsables existent et ont leurs colonnes id en PRIMARY KEY

-- Vérifier si la contrainte fk_user_favorites_user_id existe déjà
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'user_favorites'
  AND CONSTRAINT_NAME = 'fk_user_favorites_user_id'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `user_favorites` ADD CONSTRAINT `fk_user_favorites_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE', 'SELECT "La contrainte fk_user_favorites_user_id existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Vérifier si la contrainte fk_user_favorites_responsable_id existe déjà
SET @exist := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
  WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'user_favorites'
  AND CONSTRAINT_NAME = 'fk_user_favorites_responsable_id'
);

SET @query := IF(@exist = 0, 'ALTER TABLE `user_favorites` ADD CONSTRAINT `fk_user_favorites_responsable_id` FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`) ON DELETE CASCADE', 'SELECT "La contrainte fk_user_favorites_responsable_id existe déjà"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Ajouter la colonne updated_at à la table agents si elle n'existe pas
ALTER TABLE agents ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Mettre à jour la valeur de updated_at pour toutes les lignes existantes
UPDATE agents SET updated_at = CURRENT_TIMESTAMP WHERE updated_at IS NULL;

-- Fin du script de mise à jour
SELECT 'Mise à jour de la base de données terminée avec succès' as message; 
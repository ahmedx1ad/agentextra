-- Script pour ajouter les colonnes email et username à la table users
ALTER TABLE `users` ADD COLUMN `email` VARCHAR(255) NULL AFTER `password_hash`;
ALTER TABLE `users` ADD COLUMN `username` VARCHAR(50) NULL AFTER `email`;

-- Ajouter des index uniques pour garantir l'unicité des emails et usernames
ALTER TABLE `users` ADD UNIQUE INDEX `idx_email` (`email`);
ALTER TABLE `users` ADD UNIQUE INDEX `idx_username` (`username`);

-- Ajouter une colonne updated_at si elle n'existe pas déjà
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; 
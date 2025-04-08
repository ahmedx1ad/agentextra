-- SQL to modify user_favorites table to support both agents and responsables

-- First, alter the agent_id column to be nullable
ALTER TABLE `user_favorites` MODIFY `agent_id` INT NULL;

-- Add the responsable_id column
ALTER TABLE `user_favorites` ADD COLUMN `responsable_id` INT NULL AFTER `agent_id`;

-- Add a foreign key constraint
ALTER TABLE `user_favorites` ADD CONSTRAINT `fk_favorites_responsable` 
    FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`) ON DELETE CASCADE;

-- Add a unique key for user_id and responsable_id
ALTER TABLE `user_favorites` ADD UNIQUE KEY `user_responsable_unique` (`user_id`, `responsable_id`);

-- This SQL script modifies the user_favorites table to support both agent and responsable favorites
-- It makes agent_id nullable, adds the responsable_id column, and adds appropriate constraints 
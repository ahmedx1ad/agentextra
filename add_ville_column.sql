-- SQL to add 'ville' column to the 'responsables' table
ALTER TABLE `responsables` ADD COLUMN `ville` VARCHAR(100) NULL AFTER `service_id`;

-- This will add the 'ville' column to the table, making it nullable
-- and positioning it after the 'service_id' column. 
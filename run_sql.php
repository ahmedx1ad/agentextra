<?php
// Database connection parameters
$host = 'localhost';
$db   = 'agentextra';
$user = 'root';
$pass = '';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.<br>";
    
    // SQL statements to modify the user_favorites table
    $sql = [
        // First, check if the responsable_id column already exists
        "SELECT COUNT(*) as column_exists 
         FROM information_schema.columns 
         WHERE table_schema = '$db' 
         AND table_name = 'user_favorites' 
         AND column_name = 'responsable_id'",
         
        // Modify agent_id to be nullable if needed
        "ALTER TABLE `user_favorites` MODIFY `agent_id` INT NULL",
        
        // Add responsable_id column if it doesn't exist
        "ALTER TABLE `user_favorites` ADD COLUMN `responsable_id` INT NULL AFTER `agent_id`",
        
        // Add foreign key constraint
        "ALTER TABLE `user_favorites` ADD CONSTRAINT `fk_favorites_responsable` 
        FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`) ON DELETE CASCADE",
        
        // Add unique key
        "ALTER TABLE `user_favorites` ADD UNIQUE KEY `user_responsable_unique` (`user_id`, `responsable_id`)"
    ];
    
    // First check if the column exists
    $stmt = $pdo->query($sql[0]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $columnExists = (int)$row['column_exists'];
    
    if ($columnExists > 0) {
        echo "The responsable_id column already exists in the user_favorites table.<br>";
    } else {
        // Execute the remaining SQL statements
        for ($i = 1; $i < count($sql); $i++) {
            try {
                $pdo->exec($sql[$i]);
                echo "SQL executed successfully: " . substr($sql[$i], 0, 50) . "...<br>";
            } catch (PDOException $e) {
                echo "Error executing SQL: " . $e->getMessage() . "<br>";
            }
        }
        echo "Database structure has been updated successfully.<br>";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 
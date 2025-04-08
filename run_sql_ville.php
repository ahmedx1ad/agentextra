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
    
    // Check if the ville column already exists
    $stmt = $pdo->query("SELECT COUNT(*) as column_exists 
                         FROM information_schema.columns 
                         WHERE table_schema = '$db' 
                         AND table_name = 'responsables' 
                         AND column_name = 'ville'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $columnExists = (int)$row['column_exists'];
    
    if ($columnExists > 0) {
        echo "The ville column already exists in the responsables table.<br>";
    } else {
        // Add the ville column
        $sql = "ALTER TABLE `responsables` ADD COLUMN `ville` VARCHAR(100) NULL AFTER `service_id`";
        $pdo->exec($sql);
        echo "The ville column has been added to the responsables table.<br>";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 
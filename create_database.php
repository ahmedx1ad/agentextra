<?php
// Script to create the database if it doesn't exist

// Database connection settings
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = '';
$charset = 'utf8mb4';
$dbname = 'agentextra';

echo "<h1>Database Creation Utility</h1>";
echo "<p>This script will create the 'agentextra' database if it doesn't exist.</p>";

try {
    // Connect to MySQL without specifying a database
    $dsn = "mysql:host=$host;port=$port;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>Connected to MySQL server successfully!</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Database '$dbname' already exists.</p>";
    } else {
        // Create the database
        echo "<p>Database '$dbname' doesn't exist. Creating it now...</p>";
        
        $pdo->exec("CREATE DATABASE `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        echo "<p style='color:green'>✓ Database '$dbname' created successfully!</p>";
    }
    
    // Now connect to the database to create initial tables if needed
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if agents table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'agents'");
    
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green'>✓ Table 'agents' already exists.</p>";
    } else {
        // Create a basic agents table
        echo "<p>Creating 'agents' table...</p>";
        
        $pdo->exec("CREATE TABLE `agents` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nom` varchar(100) NOT NULL,
            `service` varchar(100) NOT NULL,
            `niveau` int(11) NOT NULL DEFAULT 1,
            `experience` int(11) NOT NULL DEFAULT 0,
            `taille` decimal(3,2) NOT NULL,
            `permis` tinyint(1) NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p style='color:green'>✓ Table 'agents' created successfully!</p>";
    }
    
    echo "<p style='font-weight:bold;color:green'>Database setup completed successfully!</p>";
    echo "<p>You can now <a href='index.php'>go back to the application</a>.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running in XAMPP Control Panel and the root user doesn't have a password set.</p>";
}
?> 
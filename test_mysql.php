<?php
// Script to check MySQL configuration

try {
    // Connect to MySQL
    $db = new PDO('mysql:host=localhost;dbname=agentextra', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Successfully connected to the database.\n\n";
    
    // Check MySQL timeout settings
    echo "MySQL Timeout Settings:\n";
    $stmt = $db->query("SHOW VARIABLES LIKE '%timeout%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
    }
    
    // Check MySQL max packet size
    echo "\nMySQL Max Packet Size:\n";
    $stmt = $db->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
    
    // Check MySQL wait_timeout
    echo "\nMySQL Wait Timeout:\n";
    $stmt = $db->query("SHOW VARIABLES LIKE 'wait_timeout'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
    
    // Check MySQL Connection Status
    echo "\nMySQL Connection Status:\n";
    $stmt = $db->query("SHOW STATUS LIKE 'Conn%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Variable_name'] . ' = ' . $row['Value'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 
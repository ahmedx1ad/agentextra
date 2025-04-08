<?php
// Simple script to test database connection

// Original settings from DB.php
$host = '127.0.0.1';
$port = '3307';          // This might be the issue - default is usually 3306
$dbname = 'agentextra';  // Check if this database exists
$username = 'root';
$password = '';
$charset = 'utf8mb4';

echo "Testing connection to MySQL with the following settings:<br>";
echo "Host: $host<br>";
echo "Port: $port<br>";
echo "Database: $dbname<br>";
echo "Username: $username<br>";
echo "<hr>";

// Try default port 3306 first
try {
    echo "Attempting connection on default port 3306...<br>";
    $dsn = "mysql:host=$host;port=3306;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<strong style='color:green'>✓ CONNECTION SUCCESSFUL on port 3306!</strong><br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<strong style='color:green'>✓ Database '$dbname' exists!</strong><br>";
    } else {
        echo "<strong style='color:orange'>⚠ Database '$dbname' does not exist!</strong><br>";
    }
} catch (PDOException $e) {
    echo "<strong style='color:red'>✗ Connection failed on port 3306: " . $e->getMessage() . "</strong><br>";
}

echo "<hr>";

// Try configured port 3307
try {
    echo "Attempting connection on configured port $port...<br>";
    $dsn = "mysql:host=$host;port=$port;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<strong style='color:green'>✓ CONNECTION SUCCESSFUL on port $port!</strong><br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<strong style='color:green'>✓ Database '$dbname' exists!</strong><br>";
    } else {
        echo "<strong style='color:orange'>⚠ Database '$dbname' does not exist!</strong><br>";
    }
} catch (PDOException $e) {
    echo "<strong style='color:red'>✗ Connection failed on port $port: " . $e->getMessage() . "</strong><br>";
}

echo "<hr>";
echo "Recommendations based on test results:<br>";
echo "1. Make sure MySQL is running in XAMPP Control Panel<br>";
echo "2. Edit app/Config/DB.php and use the correct port (probably 3306 instead of 3307)<br>";
echo "3. Make sure the 'agentextra' database exists - create it if needed<br>"; 
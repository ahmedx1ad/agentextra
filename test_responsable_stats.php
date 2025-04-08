<?php
// Test script to verify Responsable model's getStatistics method

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views');

echo "Constants defined. ROOT_PATH: " . ROOT_PATH . "\n";

// Include necessary files
try {
    echo "Loading app/Config/DB.php...\n";
    if (!file_exists('app/Config/DB.php')) {
        echo "ERROR: app/Config/DB.php does not exist\n";
        exit(1);
    }
    require_once 'app/Config/DB.php';
    
    echo "Loading app/models/Responsable.php...\n";
    if (!file_exists('app/models/Responsable.php')) {
        echo "ERROR: app/models/Responsable.php does not exist\n";
        exit(1);
    }
    require_once 'app/models/Responsable.php';
    
    echo "Files loaded successfully.\n";
} catch (Exception $e) {
    echo "ERROR during file inclusion: " . $e->getMessage() . "\n";
    exit(1);
}

// Start a session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create a test function
function testResponsableStatistics() {
    echo "Running test for Responsable::getStatistics()...\n";
    
    try {
        // Initialize the Responsable model
        echo "Creating Responsable model instance...\n";
        $responsableModel = new app\models\Responsable();
        
        // Call the getStatistics method that had the ville column issue
        echo "Calling getStatistics()...\n";
        $stats = $responsableModel->getStatistics();
        
        // If we got here without exceptions, the method works
        echo "SUCCESS! The getStatistics method executed without errors.\n";
        
        // Print the results
        echo "Statistics results:\n";
        echo "- Total responsables: " . $stats['total'] . "\n";
        echo "- Services breakdown: " . count($stats['par_service']) . " services found\n";
        echo "- Recent responsables: " . count($stats['recents']) . " recent entries\n";
        
        return true;
    } catch (Exception $e) {
        echo "ERROR! An exception occurred: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
        return false;
    }
}

// Run the test
echo "==== Testing Responsable Model ====\n";
$result = testResponsableStatistics();
echo "==== Test " . ($result ? "PASSED" : "FAILED") . " ====\n"; 
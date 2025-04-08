<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Simuler une requête HTTP
$_SERVER['REQUEST_URI'] = '/agentextra/rapports/export?entity_type=agents&format=csv';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['entity_type'] = 'agents';
$_GET['format'] = 'csv';

// Inclure le fichier index.php pour traiter la requête
require_once __DIR__ . '/index.php'; 
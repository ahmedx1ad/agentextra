<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Simuler une requête HTTP
$_SERVER['REQUEST_URI'] = '/agentextra/agents/simple-selection';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Inclure le fichier index.php pour traiter la requête
require_once __DIR__ . '/index.php'; 
<?php
// Charger le bootstrap
require_once __DIR__ . '/bootstrap.php';

// Obtenir l'URI de la requête
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/agentextra/', '', $uri);
$uri = trim($uri, '/');

// Routes définies
$routes = [
    // Auth routes
    '' => ['controller' => 'AuthController', 'action' => 'showLoginForm'],
    'auth/login' => ['controller' => 'AuthController', 'action' => 'login', 'method' => 'POST'],
    'auth/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    
    // Synchronisation route
    'sync' => ['controller' => 'SyncController', 'action' => 'synchronize'],
    
    // Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index'],
    
    // Routes des paramètres
    'settings' => ['controller' => 'SettingsController', 'action' => 'index'],
    'settings/update' => ['controller' => 'SettingsController', 'action' => 'update', 'method' => 'POST'],
    'settings/clear-cache' => ['controller' => 'SettingsController', 'action' => 'ajaxClearCache', 'method' => 'POST'],
    'settings/export' => ['controller' => 'SettingsController', 'action' => 'exportSettings'],
    'settings/import' => ['controller' => 'SettingsController', 'action' => 'importSettings', 'method' => 'POST'],
    'settings/reset' => ['controller' => 'SettingsController', 'action' => 'resetSettings'],
    
    // Routes du profil utilisateur
    'profile' => ['controller' => 'ProfileController', 'action' => 'index'],
    'profile/update' => ['controller' => 'ProfileController', 'action' => 'update'],
    'profile/change-password' => ['controller' => 'ProfileController', 'action' => 'changePassword'],
    
    // Agents routes
    'agents' => ['controller' => 'AgentController', 'action' => 'index'],
    'agents/create' => ['controller' => 'AgentController', 'action' => 'create'],
    'agents/store' => ['controller' => 'AgentController', 'action' => 'store', 'method' => 'POST'],
    'agents/edit/{id}' => ['controller' => 'AgentController', 'action' => 'edit'],
    'agents/update/{id}' => ['controller' => 'AgentController', 'action' => 'update', 'method' => 'POST'],
    'agents/delete/{id}' => ['controller' => 'AgentController', 'action' => 'delete'],
    'agents/view/{id}' => ['controller' => 'AgentController', 'action' => 'view'],
    'agents/toggle-status/{id}' => ['controller' => 'AgentController', 'action' => 'toggleStatus'],
    'agents/selection' => ['controller' => 'AgentController', 'action' => 'selection'],
    'agents/export-selection' => ['controller' => 'AgentController', 'action' => 'exportSelection'],
    'agents/calculate-scores' => ['controller' => 'AgentController', 'action' => 'calculateScores'],
    'agents/update-coefficients' => ['controller' => 'AgentController', 'action' => 'updateCoefficients', 'method' => 'POST'],
    'agents/export' => ['controller' => 'AgentController', 'action' => 'exportAgents', 'method' => 'POST'],
    'agents/classement' => ['controller' => 'AgentController', 'action' => 'classement', 'method' => 'POST'],
    'agents/bulkAction' => ['controller' => 'AgentController', 'action' => 'bulkAction', 'method' => 'POST'],
    'agents/import' => ['controller' => 'AgentController', 'action' => 'importForm'],
    'agents/do-import' => ['controller' => 'AgentController', 'action' => 'doImport'],
    
    // Nouvelles routes pour exports
    'exports/agents' => ['controller' => 'ExportController', 'action' => 'showAgentsExportPage'],
    'exports/responsables' => ['controller' => 'ExportController', 'action' => 'showResponsablesExportPage'],
    'exports/success' => ['controller' => 'ExportController', 'action' => 'showSuccessPage'],
    'responsables/export' => ['controller' => 'ExportController', 'action' => 'exportResponsables'],
    
    // Responsables routes
    'responsables' => ['controller' => 'ResponsableController', 'action' => 'index'],
    'responsables/add' => ['controller' => 'ResponsableController', 'action' => 'add'],
    'responsables/create' => ['controller' => 'ResponsableController', 'action' => 'create', 'method' => 'POST'],
    'responsables/store' => ['controller' => 'ResponsableController', 'action' => 'create', 'method' => 'POST'],
    'responsables/edit/{id}' => ['controller' => 'ResponsableController', 'action' => 'edit'],
    'responsables/update/{id}' => ['controller' => 'ResponsableController', 'action' => 'update', 'method' => 'POST'],
    'responsables/delete/{id}' => ['controller' => 'ResponsableController', 'action' => 'delete'],
    'responsables/view/{id}' => ['controller' => 'ResponsableController', 'action' => 'view'],
    'responsables/toggle_favorite' => ['controller' => 'ResponsableController', 'action' => 'toggle_favorite', 'method' => 'POST'],
    
    // Services routes
    'services' => ['controller' => 'ServiceController', 'action' => 'index'],
    'services/create' => ['controller' => 'ServiceController', 'action' => 'create'],
    'services/store' => ['controller' => 'ServiceController', 'action' => 'store', 'method' => 'POST'],
    'services/edit/{id}' => ['controller' => 'ServiceController', 'action' => 'edit'],
    'services/update/{id}' => ['controller' => 'ServiceController', 'action' => 'update', 'method' => 'POST'],
    'services/delete/{id}' => ['controller' => 'ServiceController', 'action' => 'delete'],
    'services/view/{id}' => ['controller' => 'ServiceController', 'action' => 'view'],
    
    // Users routes
    'users' => ['controller' => 'UserController', 'action' => 'index'],
    'users/create' => ['controller' => 'UserController', 'action' => 'create'],
    'users/store' => ['controller' => 'UserController', 'action' => 'store', 'method' => 'POST'],
    'users/edit/{id}' => ['controller' => 'UserController', 'action' => 'edit'],
    'users/update/{id}' => ['controller' => 'UserController', 'action' => 'update', 'method' => 'POST'],
    'users/change-password/{id}' => ['controller' => 'UserController', 'action' => 'changePassword'],
    'users/update-password/{id}' => ['controller' => 'UserController', 'action' => 'updatePassword', 'method' => 'POST'],
    'users/delete/{id}' => ['controller' => 'UserController', 'action' => 'delete'],
    'users/export' => ['controller' => 'UserController', 'action' => 'export', 'method' => 'POST'],
    
    // Routes AJAX pour les ajouts rapides via modales
    'services/store-ajax' => ['controller' => 'ServiceController', 'action' => 'storeAjax', 'method' => 'POST'],
    'responsables/store-ajax' => ['controller' => 'ResponsableController', 'action' => 'storeAjax', 'method' => 'POST'],
];

// Fonction pour faire correspondre l'URI avec une route
function matchRoute($uri, $routes) {
    static $routeCache = [];
    
    // Vérifier le cache
    $cacheKey = $uri;
    if (isset($routeCache[$cacheKey])) {
        return $routeCache[$cacheKey];
    }
    
    // Nettoyer et valider l'URI
    $uri = filter_var(trim($uri, '/'), FILTER_SANITIZE_URL);
    if ($uri === false) {
        return null;
    }
    
    // Si la route existe directement
    if (isset($routes[$uri])) {
        $routeCache[$cacheKey] = ['route' => $routes[$uri], 'params' => []];
        return $routeCache[$cacheKey];
    }
    
    $uriParts = explode('/', $uri);
    
    foreach ($routes as $route => $handler) {
        $route = trim($route, '/');
        $routeParts = explode('/', $route);
        
        if (count($routeParts) !== count($uriParts)) {
            continue;
        }
        
        $params = [];
        $match = true;
        
        for ($i = 0; $i < count($routeParts); $i++) {
            if (preg_match('/^{(.+)}$/', $routeParts[$i], $matches)) {
                $paramName = $matches[1];
                $paramValue = urldecode($uriParts[$i]);
                
                // Validation stricte des paramètres
                switch ($paramName) {
                    case 'id':
                        if (!preg_match('/^[1-9][0-9]*$/', $paramValue)) {
                            $match = false;
                            break 2;
                        }
                        break;
                    default:
                        // Validation générique pour les autres paramètres
                        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $paramValue)) {
                            $match = false;
                            break 2;
                        }
                }
                
                $params[$paramName] = $paramValue;
                continue;
            }
            
            if ($routeParts[$i] !== $uriParts[$i]) {
                $match = false;
                break;
            }
        }
        
        if ($match) {
            $result = ['route' => $handler, 'params' => $params];
            $routeCache[$cacheKey] = $result;
            return $result;
        }
    }
    
    $routeCache[$cacheKey] = null;
    return null;
}

// Trouver la route correspondante
$match = matchRoute($uri, $routes);

if ($match) {
    $route = $match['route'];
    $params = $match['params'];
    
    // Vérifier la méthode HTTP
    if (isset($route['method']) && $_SERVER['REQUEST_METHOD'] !== $route['method']) {
        header('HTTP/1.1 405 Method Not Allowed');
        header('Allow: ' . $route['method']);
        require VIEWS_PATH . '/errors/405.php';
        exit;
    }
    
    // Charger le contrôleur
    $controllerName = "App\\Controllers\\" . $route['controller'];
    $actionName = $route['action'];
    
    try {
        if (!class_exists($controllerName)) {
            throw new Exception("Controller not found: $controllerName");
        }
        
        if (!method_exists($controllerName, $actionName)) {
            throw new Exception("Action not found: $actionName");
        }
        
        $db = \App\Config\DB::getInstance();
        $controller = new $controllerName($db);
        
        // Appeler l'action avec les paramètres
        if (empty($params)) {
            $controller->$actionName();
        } else {
            call_user_func_array([$controller, $actionName], $params);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        
        if (APP_ENV === 'development') {
            throw $e;
        }
        
        header('HTTP/1.1 500 Internal Server Error');
        require VIEWS_PATH . '/errors/500.php';
    }
} else {
    header('HTTP/1.1 404 Not Found');
    require VIEWS_PATH . '/errors/404.php';
} 
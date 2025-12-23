<?php
/**
 * Point d'entrée principal de l'API
 * Redirige vers les endpoints appropriés
 */

require_once 'config/cors.php';

// Gérer les requêtes OPTIONS (preflight CORS) AVANT le routing
// Les headers CORS sont déjà définis dans cors.php
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Récupérer l'URL demandée
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Supprimer le préfixe /api/ si présent
$path = str_replace('/api', '', $path);
$path = ltrim($path, '/');

// Diviser le chemin en segments
$segments = explode('/', $path);

// Router vers les endpoints appropriés
if (empty($segments[0])) {
    // Page d'accueil de l'API
    http_response_code(200);
    echo json_encode([
        'message' => 'API ALRCF Association',
        'version' => '1.0.0',
        'endpoints' => [
            'auth' => '/api/auth/',
            'news' => '/api/news/',
            'events' => '/api/events/',
            'contact' => '/api/contact/',
            'reports' => '/api/reports/',
            'subscriptions' => '/api/subscriptions/'
        ]
    ]);
} else {
    $endpoint = $segments[0];
    
    switch ($endpoint) {
        case 'auth':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'login':
                        include 'auth/login.php';
                        break;
                    case 'register':
                        include 'auth/register.php';
                        break;
                    case 'profile':
                        include 'auth/profile.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Endpoint non trouvé']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Action requise']);
            }
            break;
            
        case 'news':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'news/get.php';
                        break;
                    case 'create':
                        include 'news/create.php';
                        break;
                    case 'update':
                        include 'news/update.php';
                        break;
                    case 'delete':
                        include 'news/delete.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'news/get.php';
            }
            break;
            
        case 'events':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'events/get.php';
                        break;
                    case 'create':
                        include 'events/create.php';
                        break;
                    case 'update':
                        include 'events/update.php';
                        break;
                    case 'delete':
                        include 'events/delete.php';
                        break;
                    case 'register':
                        include 'events/register.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'events/get.php';
            }
            break;
            
        case 'contact':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'send':
                        include 'contact/send.php';
                        break;
                    case 'get':
                        include 'contact/get.php';
                        break;
                    case 'update':
                        include 'contact/update.php';
                        break;
                    case 'delete':
                        include 'contact/delete.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'contact/send.php';
            }
            break;
            
        case 'reports':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'reports/get.php';
                        break;
                    case 'create':
                        include 'reports/create.php';
                        break;
                    case 'update':
                        include 'reports/update.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'reports/get.php';
            }
            break;
            
        case 'subscriptions':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'subscriptions/get.php';
                        break;
                    case 'create':
                        include 'subscriptions/create.php';
                        break;
                    case 'update':
                        include 'subscriptions/update.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'subscriptions/get.php';
            }
            break;
            
        case 'statistics':
            if (isset($segments[1]) && $segments[1] === 'get') {
                include 'statistics/get.php';
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Action non trouvée']);
            }
            break;

        case 'admin':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'users':
                        include 'admin/users.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Action requise pour l\'endpoint admin']);
            }
            break;
            
        case 'announcements':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'announcements/get.php';
                        break;
                    case 'create':
                        include 'announcements/create.php';
                        break;
                    case 'update':
                        include 'announcements/update.php';
                        break;
                    case 'delete':
                        include 'announcements/delete.php';
                        break;
                    case 'validate':
                        include 'announcements/validate.php';
                        break;
                    case 'upload':
                        include 'announcements/upload.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'announcements/get.php';
            }
            break;
            
        case 'projects':
            if (isset($segments[1])) {
                $action = $segments[1];
                switch ($action) {
                    case 'get':
                        include 'projects/get.php';
                        break;
                    case 'create':
                        include 'projects/create.php';
                        break;
                    case 'update':
                        include 'projects/update.php';
                        break;
                    case 'delete':
                        include 'projects/delete.php';
                        break;
                    default:
                        http_response_code(404);
                        echo json_encode(['error' => 'Action non trouvée']);
                }
            } else {
                include 'projects/get.php';
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint non trouvé']);
    }
}
?>

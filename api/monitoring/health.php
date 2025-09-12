<?php
/**
 * Endpoint de monitoring de santé de l'API
 * GET /api/monitoring/health.php
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0',
    'checks' => []
];

// Vérifier la connexion à la base de données
try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $health['checks']['database'] = [
            'status' => 'ok',
            'message' => 'Base de données accessible'
        ];
    } else {
        $health['checks']['database'] = [
            'status' => 'error',
            'message' => 'Base de données non accessible'
        ];
        $health['status'] = 'error';
    }
} catch (Exception $e) {
    $health['checks']['database'] = [
        'status' => 'error',
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ];
    $health['status'] = 'error';
}

// Vérifier l'espace disque
$diskSpace = disk_free_space('.');
$diskTotal = disk_total_space('.');
$diskUsage = (($diskTotal - $diskSpace) / $diskTotal) * 100;

$health['checks']['disk'] = [
    'status' => $diskUsage > 90 ? 'warning' : 'ok',
    'usage_percent' => round($diskUsage, 2),
    'free_space' => round($diskSpace / 1024 / 1024 / 1024, 2) . ' GB'
];

if ($diskUsage > 90) {
    $health['status'] = 'warning';
}

// Vérifier la mémoire PHP
$memoryUsage = memory_get_usage(true);
$memoryLimit = ini_get('memory_limit');
$memoryPercent = ($memoryUsage / $this->parseMemoryLimit($memoryLimit)) * 100;

$health['checks']['memory'] = [
    'status' => $memoryPercent > 80 ? 'warning' : 'ok',
    'usage_percent' => round($memoryPercent, 2),
    'current_usage' => round($memoryUsage / 1024 / 1024, 2) . ' MB',
    'limit' => $memoryLimit
];

if ($memoryPercent > 80) {
    $health['status'] = 'warning';
}

// Vérifier les extensions PHP requises
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    $health['checks']['extensions'] = [
        'status' => 'error',
        'message' => 'Extensions manquantes: ' . implode(', ', $missingExtensions)
    ];
    $health['status'] = 'error';
} else {
    $health['checks']['extensions'] = [
        'status' => 'ok',
        'message' => 'Toutes les extensions requises sont présentes'
    ];
}

// Vérifier les permissions d'écriture
$writablePaths = ['../logs', '../uploads'];
$permissionIssues = [];

foreach ($writablePaths as $path) {
    if (file_exists($path) && !is_writable($path)) {
        $permissionIssues[] = $path;
    }
}

if (!empty($permissionIssues)) {
    $health['checks']['permissions'] = [
        'status' => 'warning',
        'message' => 'Problèmes de permissions: ' . implode(', ', $permissionIssues)
    ];
    if ($health['status'] === 'ok') {
        $health['status'] = 'warning';
    }
} else {
    $health['checks']['permissions'] = [
        'status' => 'ok',
        'message' => 'Permissions correctes'
    ];
}

// Définir le code de réponse HTTP
$httpCode = 200;
if ($health['status'] === 'error') {
    $httpCode = 500;
} elseif ($health['status'] === 'warning') {
    $httpCode = 200; // Warning mais fonctionnel
}

http_response_code($httpCode);
echo json_encode($health, JSON_PRETTY_PRINT);

function parseMemoryLimit($limit) {
    $limit = trim($limit);
    $last = strtolower($limit[strlen($limit)-1]);
    $limit = (int) $limit;
    
    switch($last) {
        case 'g':
            $limit *= 1024;
        case 'm':
            $limit *= 1024;
        case 'k':
            $limit *= 1024;
    }
    
    return $limit;
}
?>

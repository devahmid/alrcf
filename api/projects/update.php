<?php
/**
 * Endpoint pour mettre à jour un projet
 * PUT /api/projects/update.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier l'authentification
    $user = verifyToken($pdo);
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    // Récupérer les données
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID du projet requis']);
        exit;
    }

    $id = (int)$data['id'];

    // Construire la requête de mise à jour dynamiquement
    $fields = [];
    $params = [':id' => $id];

    $allowedFields = [
        'title', 'description', 'category', 'status', 'priority',
        'startDate', 'endDate', 'budget', 'imageUrl',
        'assignedTo', 'progress', 'isPublic'
    ];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if ($field === 'isPublic' || $field === 'progress') {
                $fields[] = "$field = :$field";
                $params[":$field"] = $field === 'isPublic' ? ($data[$field] ? 1 : 0) : (int)$data[$field];
            } elseif ($field === 'budget') {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field] !== null ? (float)$data[$field] : null;
            } elseif ($field === 'assignedTo') {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field] !== null ? (int)$data[$field] : null;
            } else {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field] !== null ? $data[$field] : null;
            }
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
        exit;
    }

    $sql = "UPDATE projects SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => 'Projet mis à jour avec succès'
    ]);

} catch (Exception $e) {
    error_log("Erreur projects/update.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>



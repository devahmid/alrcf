<?php
/**
 * Endpoint pour créer un projet
 * POST /api/projects/create.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    
    // Validation
    if (empty($data['title']) || empty($data['description'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Titre et description requis']);
        exit;
    }

    // Préparer les données
    $title = $data['title'];
    $description = $data['description'];
    $category = $data['category'] ?? 'autre';
    $status = $data['status'] ?? 'planning';
    $priority = $data['priority'] ?? 'medium';
    $startDate = !empty($data['startDate']) ? $data['startDate'] : null;
    $endDate = !empty($data['endDate']) ? $data['endDate'] : null;
    $budget = isset($data['budget']) ? (float)$data['budget'] : null;
    $imageUrl = $data['imageUrl'] ?? null;
    $createdBy = $user['id'];
    $assignedTo = isset($data['assignedTo']) ? (int)$data['assignedTo'] : null;
    $progress = isset($data['progress']) ? (int)$data['progress'] : 0;
    $isPublic = isset($data['isPublic']) ? (bool)$data['isPublic'] : true;

    // Insérer le projet
    $sql = "INSERT INTO projects (
        title, description, category, status, priority,
        startDate, endDate, budget, imageUrl,
        createdBy, assignedTo, progress, isPublic
    ) VALUES (
        :title, :description, :category, :status, :priority,
        :startDate, :endDate, :budget, :imageUrl,
        :createdBy, :assignedTo, :progress, :isPublic
    )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':category' => $category,
        ':status' => $status,
        ':priority' => $priority,
        ':startDate' => $startDate,
        ':endDate' => $endDate,
        ':budget' => $budget,
        ':imageUrl' => $imageUrl,
        ':createdBy' => $createdBy,
        ':assignedTo' => $assignedTo,
        ':progress' => $progress,
        ':isPublic' => $isPublic ? 1 : 0
    ]);

    $projectId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Projet créé avec succès',
        'data' => ['id' => $projectId]
    ]);

} catch (Exception $e) {
    error_log("Erreur projects/create.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>


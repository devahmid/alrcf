<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de création de signalement
 * POST /api/reports/create.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['adherentId']) || !isset($input['title']) || !isset($input['description'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID adhérent, titre et description requis']);
    exit();
}

$adherentId = $input['adherentId'];
$title = $input['title'];
$description = $input['description'];
$category = $input['category'] ?? 'other';
$priority = $input['priority'] ?? 'medium';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "INSERT INTO reports (adherentId, title, description, category, priority, status, createdAt, updatedAt) 
              VALUES (:adherentId, :title, :description, :category, :priority, 'pending', NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':adherentId', $adherentId);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':priority', $priority);
    
    if ($stmt->execute()) {
        $reportId = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Signalement créé avec succès',
            'id' => $reportId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

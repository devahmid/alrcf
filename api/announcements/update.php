<?php
/**
 * API de mise à jour d'annonce
 * PUT /api/announcements/update.php
 * L'utilisateur peut mettre à jour ses propres annonces
 * L'admin peut mettre à jour toutes les annonces
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier l'authentification
    $user = verifyToken($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentification requise']);
        exit();
    }

    $isAdmin = ($user['role'] === 'admin');

    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de l\'annonce requis']);
        exit();
    }

    $id = (int)$input['id'];

    // Vérifier que l'annonce existe
    $query = "SELECT userId, status FROM announcements WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Annonce non trouvée']);
        exit();
    }

    // Vérifier les permissions
    if (!$isAdmin && $announcement['userId'] != $user['id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas la permission de modifier cette annonce']);
        exit();
    }

    // Construire la requête de mise à jour
    $updateFields = [];
    $params = [':id' => $id];

    if (isset($input['title'])) {
        $title = trim($input['title']);
        if (strlen($title) < 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Le titre doit contenir au moins 5 caractères']);
            exit();
        }
        $updateFields[] = "title = :title";
        $params[':title'] = $title;
    }

    if (isset($input['description'])) {
        $description = trim($input['description']);
        if (strlen($description) < 20) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'La description doit contenir au moins 20 caractères']);
            exit();
        }
        $updateFields[] = "description = :description";
        $params[':description'] = $description;
    }

    if (isset($input['category'])) {
        $category = $input['category'];
        $validCategories = ['service', 'emploi', 'vente', 'location', 'autre'];
        if (!in_array($category, $validCategories)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Catégorie invalide']);
            exit();
        }
        $updateFields[] = "category = :category";
        $params[':category'] = $category;
    }

    if (isset($input['price'])) {
        $updateFields[] = "price = :price";
        $params[':price'] = $input['price'] ? floatval($input['price']) : null;
    }

    if (isset($input['contactPhone'])) {
        $updateFields[] = "contactPhone = :contactPhone";
        $params[':contactPhone'] = $input['contactPhone'] ? trim($input['contactPhone']) : null;
    }

    if (isset($input['contactEmail'])) {
        $updateFields[] = "contactEmail = :contactEmail";
        $params[':contactEmail'] = $input['contactEmail'] ? trim($input['contactEmail']) : null;
    }

    if (isset($input['imageUrl'])) {
        $updateFields[] = "imageUrl = :imageUrl";
        $params[':imageUrl'] = $input['imageUrl'] ? trim($input['imageUrl']) : null;
    }

    if (isset($input['expiresAt'])) {
        $updateFields[] = "expiresAt = :expiresAt";
        $params[':expiresAt'] = $input['expiresAt'] ? $input['expiresAt'] : null;
    }

    // Si l'annonce est modifiée par le propriétaire et était approuvée, la remettre en attente
    if (!$isAdmin && $announcement['status'] === 'approved') {
        $updateFields[] = "status = 'pending'";
        $updateFields[] = "approvedBy = NULL";
        $updateFields[] = "approvedAt = NULL";
    }

    // Admin peut modifier le statut
    if ($isAdmin && isset($input['status'])) {
        $validStatuses = ['pending', 'approved', 'rejected', 'expired'];
        if (in_array($input['status'], $validStatuses)) {
            $updateFields[] = "status = :status";
            $params[':status'] = $input['status'];
        }
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun champ à mettre à jour']);
        exit();
    }

    $updateFields[] = "updatedAt = NOW()";

    $query = "UPDATE announcements SET " . implode(', ', $updateFields) . " WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Annonce mise à jour avec succès'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (Exception $e) {
    error_log("Erreur announcements/update.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>



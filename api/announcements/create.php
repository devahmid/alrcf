<?php
/**
 * API de création d'annonce
 * POST /api/announcements/create.php
 * Nécessite authentification (utilisateur inscrit)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['title']) || !isset($input['description']) || !isset($input['category'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Titre, description et catégorie requis']);
        exit();
    }

    $title = trim($input['title']);
    $description = trim($input['description']);
    $category = $input['category'];
    $price = isset($input['price']) ? floatval($input['price']) : null;
    $contactPhone = isset($input['contactPhone']) ? trim($input['contactPhone']) : null;
    $contactEmail = isset($input['contactEmail']) ? trim($input['contactEmail']) : null;
    $imageUrl = isset($input['imageUrl']) ? trim($input['imageUrl']) : null;
    
    // Calculer la date d'expiration (30 jours par défaut)
    $expiresAt = isset($input['expiresAt']) ? $input['expiresAt'] : date('Y-m-d H:i:s', strtotime('+30 days'));

    // Validation
    if (empty($title) || strlen($title) < 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le titre doit contenir au moins 5 caractères']);
        exit();
    }

    if (empty($description) || strlen($description) < 20) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La description doit contenir au moins 20 caractères']);
        exit();
    }

    $validCategories = ['service', 'emploi', 'vente', 'location', 'autre'];
    if (!in_array($category, $validCategories)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Catégorie invalide']);
        exit();
    }

    // Si pas d'email de contact fourni, utiliser celui de l'utilisateur
    if (empty($contactEmail)) {
        $query = "SELECT email FROM users WHERE id = :userId";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':userId', $user['id']);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $contactEmail = $userData['email'] ?? null;
    }

    $query = "INSERT INTO announcements (
        userId, title, description, category, price, contactPhone, contactEmail, 
        imageUrl, status, expiresAt, createdAt, updatedAt
    ) VALUES (
        :userId, :title, :description, :category, :price, :contactPhone, :contactEmail,
        :imageUrl, 'pending', :expiresAt, NOW(), NOW()
    )";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':userId', $user['id'], PDO::PARAM_INT);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':contactPhone', $contactPhone);
    $stmt->bindParam(':contactEmail', $contactEmail);
    $stmt->bindParam(':imageUrl', $imageUrl);
    $stmt->bindParam(':expiresAt', $expiresAt);
    
    if ($stmt->execute()) {
        $announcementId = $pdo->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Annonce créée avec succès. En attente de validation par l\'administrateur.',
            'id' => (int)$announcementId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de l\'annonce']);
    }
    
} catch (Exception $e) {
    error_log("Erreur announcements/create.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>


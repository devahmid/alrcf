<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de création d'actualité
 * POST /api/news/create.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['title']) || !isset($input['content'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Titre et contenu requis']);
    exit();
}

$title = $input['title'];
$content = $input['content'];
$category = $input['category'] ?? 'general';
$isPublished = $input['isPublished'] ?? true;
$author = $input['author'] ?? 'Administrateur';

try {
    $database = createDatabase();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Récupérer l'imageUrl et videoUrl si fournis
    $imageUrl = $input['imageUrl'] ?? null;
    $videoUrl = $input['videoUrl'] ?? null;
    
    $query = "INSERT INTO news (title, content, category, author, imageUrl, videoUrl, isPublished, publishedAt, createdAt, updatedAt) 
              VALUES (:title, :content, :category, :author, :imageUrl, :videoUrl, :isPublished, NOW(), NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':author', $author);
    $stmt->bindParam(':imageUrl', $imageUrl);
    $stmt->bindParam(':videoUrl', $videoUrl);
    $stmt->bindParam(':isPublished', $isPublished, PDO::PARAM_BOOL);
    
    if ($stmt->execute()) {
        $newsId = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Actualité créée avec succès',
            'id' => $newsId
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

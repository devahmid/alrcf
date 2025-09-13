<?php
/**
 * Endpoint pour récupérer les actualités
 * GET /api/news/get.php?id=123 (optionnel)
 */

require_once '../config/database.php';
require_once '../config/cors.php';

header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier si un ID spécifique est demandé
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        $stmt = $pdo->prepare("
            SELECT n.*, u.firstName, u.lastName 
            FROM news n 
            LEFT JOIN users u ON n.authorId = u.id 
            WHERE n.id = ? AND n.isPublished = 1
        ");
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$news) {
            echo json_encode([
                'success' => false,
                'message' => 'Actualité non trouvée'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $news
        ]);
    } else {
        // Récupérer toutes les actualités publiées
        $stmt = $pdo->prepare("
            SELECT n.*, u.firstName, u.lastName 
            FROM news n 
            LEFT JOIN users u ON n.authorId = u.id 
            WHERE n.isPublished = 1 
            ORDER BY n.createdAt DESC
        ");
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $news
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur news/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
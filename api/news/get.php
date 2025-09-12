<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des actualités
 * GET /api/news/get.php
 * GET /api/news/get.php?id=1 (pour une actualité spécifique)
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier si on demande une actualité spécifique
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = $_GET['id'];
        $query = "SELECT * FROM news WHERE id = :id AND is_published = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $news = $stmt->fetch();
        
        if ($news) {
            http_response_code(200);
            echo json_encode($news);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Actualité non trouvée']);
        }
    } else {
        // Récupérer toutes les actualités publiées
        $query = "SELECT * FROM news WHERE is_published = 1 ORDER BY published_at DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $news = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode($news);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

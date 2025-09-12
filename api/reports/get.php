<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des signalements
 * GET /api/reports/get.php
 * GET /api/reports/get.php?adherentId=1 (pour un adhérent spécifique)
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier si on demande les signalements d'un adhérent spécifique
    if (isset($_GET['adherentId']) && is_numeric($_GET['adherentId'])) {
        $adherentId = $_GET['adherentId'];
        $query = "SELECT * FROM reports WHERE adherentId = :adherentId ORDER BY createdAt DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':adherentId', $adherentId);
        $stmt->execute();
        
        $reports = $stmt->fetchAll();
    } else {
        // Récupérer tous les signalements
        $query = "SELECT r.*, u.firstName, u.lastName, u.email 
                  FROM reports r 
                  LEFT JOIN users u ON r.adherentId = u.id 
                  ORDER BY r.createdAt DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $reports = $stmt->fetchAll();
    }
    
    http_response_code(200);
    echo json_encode($reports);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

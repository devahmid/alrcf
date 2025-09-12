<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des cotisations
 * GET /api/subscriptions/get.php
 * GET /api/subscriptions/get.php?adherentId=1 (pour un adhérent spécifique)
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier si on demande les cotisations d'un adhérent spécifique
    if (isset($_GET['adherentId']) && is_numeric($_GET['adherentId'])) {
        $adherentId = $_GET['adherentId'];
        $query = "SELECT * FROM subscriptions WHERE adherentId = :adherentId ORDER BY paymentDate DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':adherentId', $adherentId);
        $stmt->execute();
        
        $subscriptions = $stmt->fetchAll();
    } else {
        // Récupérer toutes les cotisations
        $query = "SELECT s.*, u.firstName, u.lastName, u.email 
                  FROM subscriptions s 
                  LEFT JOIN users u ON s.adherentId = u.id 
                  ORDER BY s.paymentDate DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $subscriptions = $stmt->fetchAll();
    }
    
    http_response_code(200);
    echo json_encode($subscriptions);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

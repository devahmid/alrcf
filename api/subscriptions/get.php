<?php
/**
 * Endpoint pour récupérer les cotisations
 * GET /api/subscriptions/get.php?adherentId=123 (optionnel)
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
    
    // Vérifier si un adherentId spécifique est demandé
    if (isset($_GET['adherentId']) && !empty($_GET['adherentId'])) {
        $adherentId = (int)$_GET['adherentId'];
        
        $stmt = $pdo->prepare("
            SELECT s.*, u.firstName, u.lastName 
            FROM subscriptions s 
            LEFT JOIN users u ON s.adherentId = u.id 
            WHERE s.adherentId = ? 
            ORDER BY s.createdAt DESC
        ");
        $stmt->execute([$adherentId]);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Récupérer toutes les cotisations
        $stmt = $pdo->prepare("
            SELECT s.*, u.firstName, u.lastName 
            FROM subscriptions s 
            LEFT JOIN users u ON s.adherentId = u.id 
            ORDER BY s.createdAt DESC
        ");
        $stmt->execute();
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $subscriptions
    ]);
    
} catch (Exception $e) {
    error_log("Erreur subscriptions/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
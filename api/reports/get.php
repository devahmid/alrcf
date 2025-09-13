<?php
/**
 * Endpoint pour récupérer les signalements
 * GET /api/reports/get.php?adherentId=123 (optionnel)
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
            SELECT r.*, u.firstName, u.lastName 
            FROM reports r 
            LEFT JOIN users u ON r.adherentId = u.id 
            WHERE r.adherentId = ? 
            ORDER BY r.createdAt DESC
        ");
        $stmt->execute([$adherentId]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Récupérer tous les signalements
        $stmt = $pdo->prepare("
            SELECT r.*, u.firstName, u.lastName 
            FROM reports r 
            LEFT JOIN users u ON r.adherentId = u.id 
            ORDER BY r.createdAt DESC
        ");
        $stmt->execute();
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $reports
    ]);
    
} catch (Exception $e) {
    error_log("Erreur reports/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
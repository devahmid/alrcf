<?php
/**
 * Endpoint pour récupérer les messages de contact
 * GET /api/contact/get.php
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
    
    // Récupérer tous les messages de contact
    $stmt = $pdo->prepare("
        SELECT * FROM contact_messages 
        ORDER BY createdAt DESC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $messages
    ]);
    
} catch (Exception $e) {
    error_log("Erreur contact/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
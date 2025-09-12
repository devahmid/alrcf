<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des messages de contact
 * GET /api/contact/get.php
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $messages = $stmt->fetchAll();
    
    http_response_code(200);
    echo json_encode($messages);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

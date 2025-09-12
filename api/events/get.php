<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des événements
 * GET /api/events/get.php
 * GET /api/events/get.php?id=1 (pour un événement spécifique)
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier si on demande un événement spécifique
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = $_GET['id'];
        $query = "SELECT * FROM events WHERE id = :id AND is_published = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $event = $stmt->fetch();
        
        if ($event) {
            http_response_code(200);
            echo json_encode($event);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Événement non trouvé']);
        }
    } else {
        // Récupérer tous les événements publiés
        $query = "SELECT * FROM events WHERE is_published = 1 ORDER BY start_date ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $events = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode($events);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

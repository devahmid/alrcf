<?php
/**
 * Endpoint pour récupérer les événements
 * GET /api/events/get.php?id=123 (optionnel)
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
            SELECT e.*, u.firstName, u.lastName 
            FROM events e 
            LEFT JOIN users u ON e.authorId = u.id 
            WHERE e.id = ? AND e.isPublic = 1
        ");
        $stmt->execute([$id]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            echo json_encode([
                'success' => false,
                'message' => 'Événement non trouvé'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $event
        ]);
    } else {
        // Récupérer tous les événements publics
        $stmt = $pdo->prepare("
            SELECT e.*, u.firstName, u.lastName 
            FROM events e 
            LEFT JOIN users u ON e.authorId = u.id 
            WHERE e.isPublic = 1 
            ORDER BY e.eventDate ASC
        ");
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $events
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur events/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
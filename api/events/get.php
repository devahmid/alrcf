<?php
/**
 * Endpoint pour récupérer les événements
 * GET /api/events/get.php?id=123 (optionnel)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';

header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier si l'utilisateur est authentifié (optionnel pour les événements publics)
    $isAdmin = false;
    $user = null;
    
    // Essayer de vérifier le token si présent (pour les admins)
    if (isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_GET['token'])) {
        require_once __DIR__ . '/../auth/middleware.php';
        try {
            $user = verifyToken($pdo);
            $isAdmin = ($user && $user['role'] === 'admin');
        } catch (Exception $e) {
            // Token invalide ou absent, continuer en mode public
            $isAdmin = false;
        }
    }
    
    // Vérifier si un ID spécifique est demandé
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        $sql = "SELECT e.* FROM events e WHERE e.id = ?";
                
        if (!$isAdmin) {
            $sql .= " AND e.isPublished = 1";
        }

        $stmt = $pdo->prepare($sql);
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
        // Récupérer les événements
        $sql = "SELECT e.* FROM events e";
        
        if (!$isAdmin) {
            $sql .= " WHERE e.isPublished = 1";
        }
        
        $sql .= " ORDER BY e.startDate ASC";

        $stmt = $pdo->prepare($sql);
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
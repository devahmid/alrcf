<?php
/**
 * Endpoint pour récupérer les projets
 * GET /api/projects/get.php?id=123 (optionnel)
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

    // Vérifier si l'utilisateur est authentifié (optionnel pour les projets publics)
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
        
        $sql = "SELECT p.*, 
                u1.firstName as createdByFirstName, u1.lastName as createdByLastName,
                u2.firstName as assignedToFirstName, u2.lastName as assignedToLastName
                FROM projects p
                LEFT JOIN users u1 ON p.createdBy = u1.id
                LEFT JOIN users u2 ON p.assignedTo = u2.id
                WHERE p.id = ?";
                
        if (!$isAdmin) {
            $sql .= " AND p.isPublic = 1";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            echo json_encode([
                'success' => false,
                'message' => 'Projet non trouvé'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $project
        ]);
    } else {
        // Récupérer les projets
        $sql = "SELECT p.*, 
                u1.firstName as createdByFirstName, u1.lastName as createdByLastName,
                u2.firstName as assignedToFirstName, u2.lastName as assignedToLastName
                FROM projects p
                LEFT JOIN users u1 ON p.createdBy = u1.id
                LEFT JOIN users u2 ON p.assignedTo = u2.id";
        
        if (!$isAdmin) {
            $sql .= " WHERE p.isPublic = 1";
        }
        
        $sql .= " ORDER BY p.createdAt DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $projects
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur projects/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>


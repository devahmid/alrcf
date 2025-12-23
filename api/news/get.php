<?php
/**
 * Endpoint pour récupérer les actualités
 * GET /api/news/get.php?id=123 (optionnel)
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

    // Vérifier si l'utilisateur est authentifié (optionnel pour les actualités publiques)
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
        
        $sql = "SELECT n.* FROM news n WHERE n.id = ?";
        
        if (!$isAdmin) {
            $sql .= " AND n.isPublished = 1";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$news) {
            echo json_encode([
                'success' => false,
                'message' => 'Actualité non trouvée'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $news
        ]);
    } else {
        // Récupérer les actualités
        $sql = "SELECT n.* FROM news n";
        
        if (!$isAdmin) {
            $sql .= " WHERE n.isPublished = 1";
        }
        
        $sql .= " ORDER BY n.createdAt DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $news
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur news/get.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
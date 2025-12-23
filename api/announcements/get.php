<?php
/**
 * Endpoint pour récupérer les annonces
 * GET /api/announcements/get.php?id=123 (optionnel)
 * GET /api/announcements/get.php?userId=123 (optionnel - pour les annonces d'un utilisateur)
 * GET /api/announcements/get.php?category=service (optionnel - filtrer par catégorie)
 * GET /api/announcements/get.php?status=approved (optionnel - filtrer par statut, admin seulement)
 * Public : retourne uniquement les annonces approuvées et publiques
 * Authentifié : peut voir ses propres annonces même en attente
 * Admin : peut voir toutes les annonces
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

try {
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier l'authentification (optionnel)
    $user = verifyToken($pdo);
    $isAdmin = ($user && $user['role'] === 'admin');
    $isAuthenticated = ($user !== false);

    // Récupérer les paramètres
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $status = isset($_GET['status']) ? $_GET['status'] : null;

    // Si un ID spécifique est demandé
    if ($id) {
        $sql = "SELECT a.*, 
                u.firstName, u.lastName, u.email as userEmail,
                admin.firstName as approvedByFirstName, admin.lastName as approvedByLastName
                FROM announcements a
                LEFT JOIN users u ON a.userId = u.id
                LEFT JOIN users admin ON a.approvedBy = admin.id
                WHERE a.id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$announcement) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Annonce non trouvée'
            ]);
            exit;
        }

        // Vérifier les permissions
        // Public : seulement si approuvée et publique
        if (!$isAuthenticated) {
            if ($announcement['status'] !== 'approved' || !$announcement['isPublic']) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Accès refusé'
                ]);
                exit;
            }
        } 
        // Utilisateur authentifié : peut voir ses propres annonces ou les annonces approuvées
        elseif (!$isAdmin) {
            $isOwner = ($announcement['userId'] == $user['id']);
            $isApproved = ($announcement['status'] === 'approved' && $announcement['isPublic']);
            
            if (!$isOwner && !$isApproved) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Accès refusé'
                ]);
                exit;
            }
        }
        // Admin : peut tout voir

        // Convertir les types
        $announcement['id'] = (int)$announcement['id'];
        $announcement['userId'] = (int)$announcement['userId'];
        $announcement['price'] = $announcement['price'] ? (float)$announcement['price'] : null;
        $announcement['isPublic'] = (bool)$announcement['isPublic'];
        $announcement['approvedBy'] = $announcement['approvedBy'] ? (int)$announcement['approvedBy'] : null;
        
        echo json_encode([
            'success' => true,
            'data' => $announcement
        ]);
    } else {
        // Récupérer la liste des annonces
        $sql = "SELECT a.*, 
                u.firstName, u.lastName, u.email as userEmail,
                admin.firstName as approvedByFirstName, admin.lastName as approvedByLastName
                FROM announcements a
                LEFT JOIN users u ON a.userId = u.id
                LEFT JOIN users admin ON a.approvedBy = admin.id
                WHERE 1=1";
        
        $params = [];

        // Filtres selon les permissions
        if (!$isAuthenticated) {
            // Public : seulement les annonces approuvées, publiques et non expirées
            $sql .= " AND a.status = 'approved' AND a.isPublic = 1 AND (a.expiresAt IS NULL OR a.expiresAt > NOW())";
        } elseif (!$isAdmin) {
            // Utilisateur authentifié : ses propres annonces OU les annonces approuvées et non expirées
            $sql .= " AND (a.userId = :currentUserId OR (a.status = 'approved' AND a.isPublic = 1 AND (a.expiresAt IS NULL OR a.expiresAt > NOW())))";
            $params[':currentUserId'] = $user['id'];
        }
        // Admin : peut tout voir (y compris les expirées)

        // Filtre par utilisateur
        if ($userId) {
            if ($isAdmin || ($isAuthenticated && $userId == $user['id'])) {
                $sql .= " AND a.userId = :userId";
                $params[':userId'] = $userId;
            }
        }

        // Filtre par catégorie
        if ($category) {
            $validCategories = ['service', 'emploi', 'vente', 'location', 'autre'];
            if (in_array($category, $validCategories)) {
                $sql .= " AND a.category = :category";
                $params[':category'] = $category;
            }
        }

        // Filtre par statut (admin seulement)
        if ($status && $isAdmin) {
            $validStatuses = ['pending', 'approved', 'rejected', 'expired'];
            if (in_array($status, $validStatuses)) {
                $sql .= " AND a.status = :status";
                $params[':status'] = $status;
            }
        }

        $sql .= " ORDER BY a.createdAt DESC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Convertir les types
        foreach ($announcements as &$announcement) {
            $announcement['id'] = (int)$announcement['id'];
            $announcement['userId'] = (int)$announcement['userId'];
            $announcement['price'] = $announcement['price'] ? (float)$announcement['price'] : null;
            $announcement['isPublic'] = (bool)$announcement['isPublic'];
            $announcement['approvedBy'] = $announcement['approvedBy'] ? (int)$announcement['approvedBy'] : null;
        }

        echo json_encode([
            'success' => true,
            'data' => $announcements
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur announcements/get.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>


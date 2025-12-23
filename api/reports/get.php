<?php
/**
 * Endpoint pour récupérer les signalements
 * GET /api/reports/get.php?adherentId=123 (optionnel)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

try {
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Auth verification
    $user = verifyToken($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non autorisé']);
        exit;
    }

    $isAdmin = ($user['role'] === 'admin');
    
    // Determine filtering scope
    $filterAdherentId = null;

    if (!$isAdmin) {
        // Non-admins can only see their own reports
        $filterAdherentId = $user['id'];
    } elseif (isset($_GET['adherentId']) && !empty($_GET['adherentId'])) {
        // Admins can filter by specific adherent if requested
        $filterAdherentId = (int)$_GET['adherentId'];
    }

    if ($filterAdherentId) {
        $stmt = $pdo->prepare("
            SELECT r.*, u.firstName, u.lastName 
            FROM reports r 
            LEFT JOIN users u ON r.adherentId = u.id 
            WHERE r.adherentId = ? 
            ORDER BY r.createdAt DESC
        ");
        $stmt->execute([$filterAdherentId]);
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Admin viewing all reports
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
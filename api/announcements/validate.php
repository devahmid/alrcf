<?php
/**
 * API de validation/rejet d'annonce (Admin uniquement)
 * POST /api/announcements/validate.php
 * Permet à l'admin d'approuver ou de rejeter une annonce
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier l'authentification et les droits admin
    $user = verifyToken($pdo);
    if (!$user || $user['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé. Droits d\'administrateur requis.']);
        exit();
    }

    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['id']) || !isset($input['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID et action requis']);
        exit();
    }

    $id = (int)$input['id'];
    $action = $input['action']; // 'approve' ou 'reject'

    if (!in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action invalide. Utilisez "approve" ou "reject"']);
        exit();
    }

    // Vérifier que l'annonce existe
    $query = "SELECT id, status FROM announcements WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Annonce non trouvée']);
        exit();
    }

    // Mettre à jour le statut
    if ($action === 'approve') {
        $status = 'approved';
        $rejectionReason = null;
    } else {
        $status = 'rejected';
        $rejectionReason = isset($input['rejectionReason']) ? trim($input['rejectionReason']) : 'Annonce rejetée par l\'administrateur';
    }

    $query = "UPDATE announcements 
              SET status = :status, 
                  approvedBy = :approvedBy, 
                  approvedAt = NOW(),
                  rejectionReason = :rejectionReason,
                  updatedAt = NOW()
              WHERE id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':approvedBy', $user['id'], PDO::PARAM_INT);
    $stmt->bindParam(':rejectionReason', $rejectionReason);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $message = $action === 'approve' 
            ? 'Annonce approuvée avec succès' 
            : 'Annonce rejetée avec succès';
        
        echo json_encode([
            'success' => true,
            'message' => $message
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
    }
    
} catch (Exception $e) {
    error_log("Erreur announcements/validate.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>


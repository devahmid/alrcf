<?php
/**
 * Endpoint pour vérifier et réinitialiser le mot de passe
 * GET /api/auth/reset-password.php?token=... (vérifier le token)
 * POST /api/auth/reset-password.php (réinitialiser avec token)
 */

require_once '../config/database.php';
require_once '../config/cors.php';

header('Content-Type: application/json');

// Vérifier le token (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Token requis']);
        exit;
    }
    
    $token = $_GET['token'];
    
    try {
        $db = createDatabase();
        $pdo = $db->getConnection();
        
        if (!$pdo) {
            throw new Exception('Erreur de connexion à la base de données');
        }
        
        // Vérifier le token
        $stmt = $pdo->prepare("
            SELECT pr.email, pr.expiresAt, pr.used, u.id, u.firstName, u.lastName
            FROM password_resets pr
            JOIN users u ON pr.email = u.email
            WHERE pr.token = ? AND pr.expiresAt > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            echo json_encode([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ]);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Token valide',
            'email' => $reset['email']
        ]);
        
    } catch (Exception $e) {
        error_log("Erreur vérification token: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erreur interne du serveur'
        ]);
    }
    exit;
}

// Réinitialiser le mot de passe (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['token']) || !isset($input['password'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Token et nouveau mot de passe requis'
            ]);
            exit;
        }
        
        $token = $input['token'];
        $newPassword = $input['password'];
        
        // Validation du mot de passe
        if (strlen($newPassword) < 6) {
            echo json_encode([
                'success' => false,
                'message' => 'Le mot de passe doit contenir au moins 6 caractères'
            ]);
            exit;
        }
        
        // Connexion à la base de données
        $db = createDatabase();
        $pdo = $db->getConnection();
        
        if (!$pdo) {
            throw new Exception('Erreur de connexion à la base de données');
        }
        
        // Vérifier le token
        $stmt = $pdo->prepare("
            SELECT pr.email, pr.expiresAt, pr.used
            FROM password_resets pr
            WHERE pr.token = ? AND pr.expiresAt > NOW() AND pr.used = 0
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            echo json_encode([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ]);
            exit;
        }
        
        // Hasher le nouveau mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Mettre à jour le mot de passe
        $updateStmt = $pdo->prepare("UPDATE users SET password = ?, updatedAt = NOW() WHERE email = ?");
        $updateStmt->execute([$hashedPassword, $reset['email']]);
        
        // Marquer le token comme utilisé
        $markUsedStmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $markUsedStmt->execute([$token]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
        
    } catch (Exception $e) {
        error_log("Erreur reset-password: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Erreur interne du serveur'
        ]);
    }
    exit;
}

// Méthode non autorisée
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
?>


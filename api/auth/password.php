<?php
/**
 * Endpoint de changement de mot de passe
 * PUT /api/auth/password.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données PUT
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id']) || !isset($input['currentPassword']) || !isset($input['newPassword'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID, mot de passe actuel et nouveau mot de passe requis'
        ]);
        exit;
    }
    
    $userId = (int)$input['id'];
    $currentPassword = $input['currentPassword'];
    $newPassword = $input['newPassword'];
    
    // Validation du nouveau mot de passe
    if (strlen($newPassword) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Le nouveau mot de passe doit contenir au moins 6 caractères'
        ]);
        exit;
    }
    
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Récupérer le mot de passe actuel
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
        exit;
    }
    
    // Vérifier le mot de passe actuel
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Mot de passe actuel incorrect'
        ]);
        exit;
    }
    
    // Hasher le nouveau mot de passe
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Mettre à jour le mot de passe
    $updateStmt = $pdo->prepare("UPDATE users SET password = ?, updatedAt = NOW() WHERE id = ?");
    
    if ($updateStmt->execute([$hashedPassword, $userId])) {
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du mot de passe'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur de changement de mot de passe: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>

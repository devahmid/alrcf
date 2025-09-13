<?php
/**
 * Endpoint de mise à jour du profil utilisateur
 * PUT /api/auth/profile.php
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
    
    if (!$input || !isset($input['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID utilisateur requis'
        ]);
        exit;
    }
    
    $userId = (int)$input['id'];
    unset($input['id']);
    
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Vérifier que l'utilisateur existe
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $checkStmt->execute([$userId]);
    
    if (!$checkStmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Utilisateur non trouvé'
        ]);
        exit;
    }
    
    // Construire la requête de mise à jour
    $allowedFields = ['firstName', 'lastName', 'email', 'phone', 'address'];
    $updateFields = [];
    $values = [];
    
    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $values[] = $input[$field];
        }
    }
    
    if (empty($updateFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun champ à mettre à jour'
        ]);
        exit;
    }
    
    // Ajouter updatedAt
    $updateFields[] = "updatedAt = NOW()";
    $values[] = $userId;
    
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($values)) {
        // Récupérer les données mises à jour
        $selectStmt = $pdo->prepare("
            SELECT id, email, role, firstName, lastName, phone, address, 
                   createdAt, updatedAt, isActive
            FROM users 
            WHERE id = ?
        ");
        $selectStmt->execute([$userId]);
        $updatedUser = $selectStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Profil mis à jour avec succès',
            'user' => $updatedUser
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Erreur de mise à jour du profil: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
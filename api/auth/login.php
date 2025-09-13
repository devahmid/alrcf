<?php
/**
 * Endpoint de connexion utilisateur
 * POST /api/auth/login.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ]);
        exit;
    }
    
    $email = trim($input['email']);
    $password = $input['password'];
    
    // Validation basique
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email et mot de passe requis'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Format d\'email invalide'
        ]);
        exit;
    }
    
    // Connexion à la base de données
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Rechercher l'utilisateur
    $stmt = $pdo->prepare("
        SELECT id, email, password, role, firstName, lastName, phone, address, 
               createdAt, updatedAt, isActive
        FROM users 
        WHERE email = ? AND isActive = 1
    ");
    
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
        exit;
    }
    
    // Vérifier le mot de passe
    if (!password_verify($password, $user['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect'
        ]);
        exit;
    }
    
    // Supprimer le mot de passe de la réponse
    unset($user['password']);
    
    // Générer un token simple (dans un vrai projet, utilisez JWT)
    $token = base64_encode(json_encode([
        'id' => $user['id'],
        'email' => $user['email'],
        'role' => $user['role'],
        'exp' => time() + (24 * 60 * 60) // 24 heures
    ]));
    
    // Mettre à jour la dernière connexion
    $updateStmt = $pdo->prepare("UPDATE users SET updatedAt = NOW() WHERE id = ?");
    $updateStmt->execute([$user['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => $user,
        'token' => $token
    ]);
    
} catch (Exception $e) {
    error_log("Erreur de connexion: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>
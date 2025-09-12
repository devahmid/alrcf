<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de connexion
 * POST /api/auth/login.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email et mot de passe requis']);
    exit();
}

$email = $input['email'];
$password = $input['password'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Rechercher l'utilisateur
    $query = "SELECT * FROM users WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Générer un token simple (dans un vrai projet, utiliser JWT)
        $token = bin2hex(random_bytes(32));
        
        // Mettre à jour le token dans la base
        $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->bindParam(':id', $user['id']);
        $updateStmt->execute();
        
        // Retourner les données utilisateur (sans le mot de passe)
        unset($user['password']);
        $user['last_login'] = date('Y-m-d H:i:s');
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user,
            'token' => $token
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

<?php
/**
 * Endpoint d'inscription utilisateur
 * POST /api/auth/register.php
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
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validation des champs requis
    if (empty($input['email']) || empty($input['password']) || empty($input['firstName']) || empty($input['lastName'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Veuillez remplir tous les champs obligatoires'
        ]);
        exit;
    }

    $email = trim($input['email']);
    $password = $input['password'];
    $firstName = trim($input['firstName']);
    $lastName = trim($input['lastName']);
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    $address = isset($input['address']) ? trim($input['address']) : '';

    // Validation email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Format d\'email invalide']);
        exit;
    }

    // Validation mot de passe (min 6 caractères)
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
        exit;
    }

    $db = createDatabase();
    $pdo = $db->getConnection();

    if (!$pdo) {
        throw new Exception('Erreur de connexion base de données');
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
        exit;
    }

    // Hashage mot de passe
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, firstName, lastName, phone, address, role, isActive, createdAt, updatedAt)
        VALUES (?, ?, ?, ?, ?, ?, 'adherent', 1, NOW(), NOW())
    ");

    $stmt->execute([$email, $hashedPassword, $firstName, $lastName, $phone, $address]);
    $userId = $pdo->lastInsertId();

    // Auto-login (générer token)
    $token = base64_encode(json_encode([
        'id' => $userId,
        'email' => $email,
        'role' => 'adherent',
        'exp' => time() + (24 * 60 * 60)
    ]));

    // Récupérer l'utilisateur créé (sans mot de passe)
    $user = [
        'id' => $userId,
        'email' => $email,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'role' => 'adherent',
        'phone' => $phone,
        'address' => $address,
        'isActive' => 1
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'user' => $user,
        'token' => $token
    ]);

} catch (Exception $e) {
    error_log("Erreur inscription: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur lors de l\'inscription'
    ]);
}
?>

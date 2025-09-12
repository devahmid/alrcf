<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API d'envoi de message de contact
 * POST /api/contact/send.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['name']) || !isset($input['email']) || !isset($input['subject']) || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nom, email, sujet et message requis']);
    exit();
}

$name = $input['name'];
$email = $input['email'];
$phone = $input['phone'] ?? null;
$subject = $input['subject'];
$message = $input['message'];

// Validation basique
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) 
              VALUES (:name, :email, :phone, :subject, :message, 'new', NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    
    if ($stmt->execute()) {
        $messageId = $db->lastInsertId();
        
        // Ici, vous pourriez envoyer un email de notification à l'admin
        // mail('admin@alrcf.fr', 'Nouveau message de contact', $message);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès',
            'id' => $messageId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

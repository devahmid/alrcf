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
    $database = createDatabase();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Séparer le nom en prénom et nom
    $nameParts = explode(' ', $name, 2);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
    
    $query = "INSERT INTO contact_messages (firstName, lastName, email, phone, subject, message, status, createdAt) 
              VALUES (:firstName, :lastName, :email, :phone, :subject, :message, 'new', NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':firstName', $firstName);
    $stmt->bindParam(':lastName', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $message);
    
    if ($stmt->execute()) {
        $messageId = $db->lastInsertId();
        
        // Tentative d'envoi d'emails (optionnel)
        $emailSent = false;
        try {
            require_once '../config/email.php';
            $emailSender = createEmailSender();
            
            // Email de notification à l'admin
            $adminEmailSent = $emailSender->sendContactNotification([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
            
            // Email de confirmation à l'expéditeur
            $confirmationEmailSent = $emailSender->sendContactConfirmation([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
            
            $emailSent = $adminEmailSent && $confirmationEmailSent;
            
        } catch (Exception $emailException) {
            // Les emails sont optionnels, on continue même en cas d'erreur
            error_log("Erreur email contact: " . $emailException->getMessage());
        }
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès',
            'id' => $messageId,
            'email_sent' => $emailSent
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

<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de mise à jour du statut d'un message de contact
 * PUT /api/contact/update.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID et statut requis']);
    exit();
}

$messageId = $input['id'];
$status = $input['status'];
$reply = $input['reply'] ?? null;

try {
    $database = createDatabase();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "UPDATE contact_messages SET status = :status, reply = :reply, repliedAt = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':reply', $reply);
    $stmt->bindParam(':id', $messageId);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            // Si une réponse a été fournie, envoyer un email de notification
            $emailSent = false;
            $emailError = null;
            if ($reply && !empty(trim($reply))) {
                try {
                    // Récupérer les informations du message pour l'email
                    // Récupérer toutes les colonnes disponibles
                    $getMessageQuery = "SELECT * FROM contact_messages WHERE id = :id";
                    $getMessageStmt = $db->prepare($getMessageQuery);
                    $getMessageStmt->bindParam(':id', $messageId);
                    $getMessageStmt->execute();
                    $messageData = $getMessageStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($messageData && isset($messageData['email']) && !empty($messageData['email'])) {
                        // Construire le nom complet (comme dans send.php)
                        $name = '';
                        if (isset($messageData['firstName']) && isset($messageData['lastName']) && 
                            !empty($messageData['firstName']) && !empty($messageData['lastName'])) {
                            $name = $messageData['firstName'] . ' ' . $messageData['lastName'];
                        } elseif (isset($messageData['name']) && !empty($messageData['name'])) {
                            $name = $messageData['name'];
                        } else {
                            $name = 'Utilisateur';
                        }
                        
                        // Utiliser EXACTEMENT la même méthode que sendContactConfirmation
                        $to = $messageData['email'];
                        $subject = "Réponse à votre message - " . $messageData['subject'];
                        
                        // Construire le message (même format que sendContactConfirmation)
                        $emailMessage = "Bonjour " . $name . ",\n\n";
                        $emailMessage .= "Nous avons bien reçu votre message concernant : " . $messageData['subject'] . "\n\n";
                        $emailMessage .= "Voici notre réponse :\n\n";
                        $emailMessage .= "=== RÉPONSE ===\n";
                        $emailMessage .= $reply . "\n\n";
                        $emailMessage .= "=== VOTRE MESSAGE ORIGINAL ===\n";
                        $emailMessage .= "Sujet: " . $messageData['subject'] . "\n";
                        $emailMessage .= "Message:\n" . $messageData['message'] . "\n\n";
                        $emailMessage .= "=== NOS COORDONNÉES ===\n";
                        $emailMessage .= "Association ALRCF\n";
                        $emailMessage .= "Email: contact@alrcf.fr\n";
                        $emailMessage .= "Site web: https://alrcf.fr\n\n";
                        $emailMessage .= "Si vous avez d'autres questions, n'hésitez pas à nous contacter.\n\n";
                        $emailMessage .= "Cordialement,\n";
                        $emailMessage .= "L'équipe ALRCF";
                        
                        // Construire les headers EXACTEMENT comme dans sendContactConfirmation
                        $fromEmail = 'contact@alrcf.fr';
                        $fromName = 'ALRCF Association';
                        $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
                        $headers .= "Reply-To: " . $fromEmail . "\r\n";
                        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        
                        // Envoyer l'email directement avec mail() comme dans sendContactConfirmation
                        $emailSent = @mail($to, $subject, $emailMessage, $headers);
                        
                        if (!$emailSent) {
                            $lastError = error_get_last();
                            $emailError = $lastError ? $lastError['message'] : 'Erreur inconnue lors de l\'envoi';
                        }
                        
                        // Écrire dans un fichier de log personnalisé
                        $logFile = __DIR__ . '/../logs/email_reply.log';
                        $logDir = dirname($logFile);
                        if (!is_dir($logDir)) {
                            mkdir($logDir, 0755, true);
                        }
                        $logMessage = date('Y-m-d H:i:s') . " - Reply email - To: " . $to . " - Subject: " . $subject . " - Sent: " . ($emailSent ? 'YES' : 'NO');
                        if ($emailError) {
                            $logMessage .= " - Error: " . $emailError;
                        }
                        $logMessage .= "\n";
                        file_put_contents($logFile, $logMessage, FILE_APPEND);
                    }
                } catch (Exception $emailException) {
                    // Les emails sont optionnels, on continue même en cas d'erreur
                    $emailError = $emailException->getMessage();
                    
                    // Écrire l'erreur dans le log
                    $logFile = __DIR__ . '/../logs/email_reply.log';
                    $logDir = dirname($logFile);
                    if (!is_dir($logDir)) {
                        mkdir($logDir, 0755, true);
                    }
                    $logMessage = date('Y-m-d H:i:s') . " - Reply email ERROR: " . $emailError . "\n";
                    file_put_contents($logFile, $logMessage, FILE_APPEND);
                }
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true, 
                'message' => 'Message mis à jour avec succès',
                'email_sent' => $emailSent,
                'email_error' => $emailError
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Message non trouvé']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

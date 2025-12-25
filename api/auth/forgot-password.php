<?php
/**
 * Endpoint pour demander la réinitialisation de mot de passe
 * POST /api/auth/forgot-password.php
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer les données POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Email requis'
        ]);
        exit;
    }
    
    $email = trim($input['email']);
    
    // Validation
    if (empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email requis'
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
    
    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT id, firstName, lastName FROM users WHERE email = ? AND isActive = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Pour des raisons de sécurité, on ne révèle pas si l'email existe ou non
    // On retourne toujours un succès, mais on n'envoie l'email que si l'utilisateur existe
    if ($user) {
        // Générer un token unique
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valide 1 heure
        
        // Supprimer les anciens tokens non utilisés pour cet email
        $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND used = 0 AND expiresAt < NOW()");
        $deleteStmt->execute([$email]);
        
        // Insérer le nouveau token
        $insertStmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiresAt) VALUES (?, ?, ?)");
        $insertStmt->execute([$email, $token, $expiresAt]);
        
        // Construire l'URL de réinitialisation
        $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
            . '://' . $_SERVER['HTTP_HOST'] 
            . '/reset-password?token=' . $token;
        
        // Envoyer l'email de réinitialisation
        try {
            $emailSender = createEmailSender();
            $name = $user['firstName'] . ' ' . $user['lastName'];
            
            $to = $email;
            $subject = "Réinitialisation de votre mot de passe - ALRCF";
            
            $message = "Bonjour " . $user['firstName'] . ",\n\n";
            $message .= "Vous avez demandé la réinitialisation de votre mot de passe pour votre compte ALRCF.\n\n";
            $message .= "Pour réinitialiser votre mot de passe, cliquez sur le lien suivant (valide pendant 1 heure) :\n\n";
            $message .= $resetUrl . "\n\n";
            $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.\n\n";
            $message .= "=== SÉCURITÉ ===\n";
            $message .= "Pour votre sécurité, ce lien expire dans 1 heure.\n";
            $message .= "Ne partagez jamais ce lien avec personne.\n\n";
            $message .= "=== NOS COORDONNÉES ===\n";
            $message .= "Association ALRCF\n";
            $message .= "Email: contact@alrcf.fr\n";
            $message .= "Site web: https://alrcf.fr\n\n";
            $message .= "Cordialement,\n";
            $message .= "L'équipe ALRCF";
            
            $fromEmail = 'contact@alrcf.fr';
            $fromName = 'ALRCF Association';
            $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
            $headers .= "Reply-To: " . $fromEmail . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            
            $emailSent = @mail($to, $subject, $message, $headers);
            
            if (!$emailSent) {
                error_log("Erreur lors de l'envoi de l'email de réinitialisation pour: " . $email);
            }
        } catch (Exception $emailException) {
            error_log("Erreur email forgot-password: " . $emailException->getMessage());
        }
    }
    
    // Toujours retourner un succès pour ne pas révéler si l'email existe
    echo json_encode([
        'success' => true,
        'message' => 'Si cet email existe dans notre système, vous recevrez un lien de réinitialisation.'
    ]);
    
} catch (Exception $e) {
    error_log("Erreur forgot-password: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur interne du serveur'
    ]);
}
?>


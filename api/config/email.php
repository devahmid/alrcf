<?php
/**
 * Configuration email pour ALRCF
 * Configuration Hostinger SMTP
 */

// Configuration SMTP Hostinger
$email_config = [
    'smtp_host' => 'smtp.hostinger.com',
    'smtp_port' => 465,
    'smtp_encryption' => 'ssl',
    'smtp_username' => 'contact@alrcf.fr', // Remplacez par votre email Hostinger
    'smtp_password' => 'Elodie14061990@', // Remplacez par votre mot de passe email
    'from_email' => 'contact@alrcf.fr',
    'from_name' => 'ALRCF Association',
    'admin_email' => 'contact@alrcf.fr', // Email de l'administrateur (même que l'expéditeur)
    'admin_name' => 'Administrateur ALRCF'
];

/**
 * Classe pour l'envoi d'emails
 */
class EmailSender {
    private $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    /**
     * Envoyer un email de notification de contact à l'admin
     */
    public function sendContactNotification($contactData) {
        $to = $this->config['admin_email'];
        $subject = "Nouveau message de contact - " . $contactData['subject'];
        
        $message = $this->buildContactEmail($contactData);
        $headers = $this->buildHeaders($contactData['email'], $contactData['name']);
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Envoyer un email de confirmation à l'expéditeur
     */
    public function sendContactConfirmation($contactData) {
        $to = $contactData['email'];
        $subject = "Confirmation de réception - " . $contactData['subject'];
        
        $message = $this->buildConfirmationEmail($contactData);
        $headers = $this->buildHeaders($this->config['from_email'], $this->config['from_name']);
        
        return mail($to, $subject, $message, $headers);
    }
    
    /**
     * Construire le contenu de l'email de notification admin
     */
    private function buildContactEmail($data) {
        $message = "Nouveau message reçu via le formulaire de contact du site ALRCF\n\n";
        $message .= "=== INFORMATIONS EXPÉDITEUR ===\n";
        $message .= "Nom: " . $data['name'] . "\n";
        $message .= "Email: " . $data['email'] . "\n";
        $message .= "Téléphone: " . ($data['phone'] ?: 'Non renseigné') . "\n\n";
        $message .= "=== MESSAGE ===\n";
        $message .= "Sujet: " . $data['subject'] . "\n";
        $message .= "Message:\n" . $data['message'] . "\n\n";
        $message .= "=== INFORMATIONS TECHNIQUES ===\n";
        $message .= "Date: " . date('d/m/Y H:i:s') . "\n";
        $message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Inconnue') . "\n";
        $message .= "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu') . "\n\n";
        $message .= "---\n";
        $message .= "Cet email a été envoyé automatiquement par le système de contact ALRCF.\n";
        $message .= "Pour répondre, utilisez l'adresse email: " . $data['email'];
        
        return $message;
    }
    
    /**
     * Construire le contenu de l'email de confirmation
     */
    private function buildConfirmationEmail($data) {
        $message = "Bonjour " . $data['name'] . ",\n\n";
        $message .= "Nous avons bien reçu votre message concernant : " . $data['subject'] . "\n\n";
        $message .= "Nous vous remercions de votre intérêt pour l'ALRCF et nous vous répondrons dans les plus brefs délais.\n\n";
        $message .= "=== RÉCAPITULATIF DE VOTRE MESSAGE ===\n";
        $message .= "Sujet: " . $data['subject'] . "\n";
        $message .= "Message:\n" . $data['message'] . "\n\n";
        $message .= "=== NOS COORDONNÉES ===\n";
        $message .= "Association ALRCF\n";
        $message .= "Email: contact@alrcf.fr\n";
        $message .= "Site web: https://alrcf.fr\n\n";
        $message .= "Cordialement,\n";
        $message .= "L'équipe ALRCF";
        
        return $message;
    }
    
    /**
     * Construire les headers de l'email
     */
    private function buildHeaders($fromEmail, $fromName) {
        $headers = "From: " . $fromName . " <" . $fromEmail . ">\r\n";
        $headers .= "Reply-To: " . $fromEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        return $headers;
    }
}

/**
 * Fonction utilitaire pour obtenir une instance EmailSender
 */
function createEmailSender(): EmailSender {
    global $email_config;
    return new EmailSender($email_config);
}
?>

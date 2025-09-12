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
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "UPDATE contact_messages SET status = :status, reply = :reply, replied_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':reply', $reply);
    $stmt->bindParam(':id', $messageId);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Message mis à jour avec succès']);
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

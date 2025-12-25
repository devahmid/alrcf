<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de mise à jour d'événement
 * PUT /api/events/update.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID événement requis']);
    exit();
}

$eventId = $input['id'];
unset($input['id']); // Retirer l'ID des données à mettre à jour

try {
    $database = createDatabase();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Construire la requête de mise à jour dynamiquement
    $fields = [];
    $values = [];
    
    // Mapping des noms de colonnes camelCase
    $allowedFields = ['title', 'description', 'startDate', 'endDate', 'location', 'imageUrl', 'maxParticipants', 'registrationRequired', 'registrationDeadline', 'isPublished'];
    
    foreach ($input as $key => $value) {
        if (in_array($key, $allowedFields)) {
            $fields[] = "`$key` = :$key";
            $values[$key] = $value;
        }
    }
    
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun champ valide à mettre à jour']);
        exit();
    }
    
    $values['id'] = $eventId;
    
    $query = "UPDATE events SET " . implode(', ', $fields) . ", updatedAt = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    
    foreach ($values as $key => $value) {
        if ($key === 'isPublished' || $key === 'registrationRequired') {
            $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
        } else {
            $stmt->bindValue(":$key", $value);
        }
    }
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Événement mis à jour avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>



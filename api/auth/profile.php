<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de mise à jour du profil
 * PUT /api/auth/profile.php
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
    echo json_encode(['success' => false, 'message' => 'ID utilisateur requis']);
    exit();
}

$userId = $input['id'];
unset($input['id']); // Retirer l'ID des données à mettre à jour

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    // Construire la requête de mise à jour dynamiquement
    $fields = [];
    $values = [];
    
    foreach ($input as $key => $value) {
        if (in_array($key, ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'postalCode', 'emergencyContact', 'emergencyPhone'])) {
            $fields[] = "`$key` = :$key";
            $values[$key] = $value;
        }
    }
    
    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun champ valide à mettre à jour']);
        exit();
    }
    
    $values['id'] = $userId;
    $values['updated_at'] = date('Y-m-d H:i:s');
    
    $query = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = :updated_at WHERE id = :id";
    $stmt = $db->prepare($query);
    
    foreach ($values as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profil mis à jour avec succès']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

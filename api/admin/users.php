<?php
// CORS handled by config/cors.php via index.php
// Note: OPTIONS requests are handled in index.php before this file is included

include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

// Gérer les requêtes OPTIONS (fallback si jamais appelé directement)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$database = createDatabase();
$pdo = $database->getConnection();

if (!$pdo) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit();
}

// Verify Admin Token
$user = verifyToken($pdo);
if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Accès refusé. Droits d'administrateur requis."]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // List all users
        $query = "SELECT id, firstName, lastName, email, phone, role, isActive, createdAt FROM users ORDER BY createdAt DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Convert types
            $row['id'] = (int)$row['id'];
            $row['isActive'] = (bool)$row['isActive'];
            $users[] = $row;
        }
        
        echo json_encode(["success" => true, "data" => $users]);
        break;

    case 'PUT':
        // Update user (role or status)
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID utilisateur manquant."]);
            exit();
        }

        // Récupérer les valeurs actuelles
        $checkQuery = "SELECT isActive, role FROM users WHERE id = :id";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
        $checkStmt->execute();
        $currentUser = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$currentUser) {
            http_response_code(404);
            echo json_encode(["success" => false, "message" => "Utilisateur non trouvé."]);
            exit();
        }

        // Utiliser les nouvelles valeurs si fournies, sinon garder les actuelles
        $newIsActive = isset($data['isActive']) ? (bool)$data['isActive'] : $currentUser['isActive'];
        $newRole = isset($data['role']) ? $data['role'] : $currentUser['role'];

        // Validation du rôle
        if (!in_array($newRole, ['admin', 'adherent'])) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Rôle invalide."]);
            exit();
        }

        // Vérifier qu'on ne retire pas le dernier admin
        if ($currentUser['role'] === 'admin' && $newRole === 'adherent') {
            $adminCountQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND id != :id AND isActive = 1";
            $adminCountStmt = $pdo->prepare($adminCountQuery);
            $adminCountStmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $adminCountStmt->execute();
            $adminCount = $adminCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($adminCount == 0) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Impossible de retirer le rôle administrateur : il doit y avoir au moins un administrateur actif."]);
                exit();
            }
        }

        // Vérifier qu'on ne désactive pas le dernier admin actif
        if ($currentUser['role'] === 'admin' && !$newIsActive) {
            $activeAdminCountQuery = "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND isActive = 1 AND id != :id";
            $activeAdminCountStmt = $pdo->prepare($activeAdminCountQuery);
            $activeAdminCountStmt->bindParam(':id', $data['id'], PDO::PARAM_INT);
            $activeAdminCountStmt->execute();
            $activeAdminCount = $activeAdminCountStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($activeAdminCount == 0) {
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Impossible de désactiver : il doit y avoir au moins un administrateur actif."]);
                exit();
            }
        }

        // Mettre à jour
        $updateQuery = "UPDATE users SET isActive = :isActive, role = :role, updatedAt = NOW() WHERE id = :id";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->bindParam(':isActive', $newIsActive, PDO::PARAM_BOOL);
        $updateStmt->bindParam(':role', $newRole);
        $updateStmt->bindParam(':id', $data['id'], PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            echo json_encode([
                "success" => true, 
                "message" => "Utilisateur mis à jour avec succès.",
                "data" => [
                    "id" => (int)$data['id'],
                    "isActive" => $newIsActive,
                    "role" => $newRole
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Impossible de mettre à jour l'utilisateur."]);
        }
        break;

    case 'DELETE':
        // Delete user
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "ID manquant."]);
            exit();
        }
        
        // Prevent deleting self (optional but good practice)
        if ($id == $user['id']) {
             http_response_code(400);
             echo json_encode(["success" => false, "message" => "Vous ne pouvez pas supprimer votre propre compte."]);
             exit();
        }

        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Utilisateur supprimé."]);
        } else {
            http_response_code(503);
            echo json_encode(["success" => false, "message" => "Impossible de supprimer l'utilisateur."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Méthode non autorisée."]);
        break;
}

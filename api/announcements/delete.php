<?php
/**
 * API de suppression d'annonce
 * DELETE /api/announcements/delete.php?id=123
 * L'utilisateur peut supprimer ses propres annonces
 * L'admin peut supprimer toutes les annonces
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    $db = createDatabase();
    $pdo = $db->getConnection();
    
    if (!$pdo) {
        throw new Exception('Erreur de connexion à la base de données');
    }

    // Vérifier l'authentification
    $user = verifyToken($pdo);
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentification requise']);
        exit();
    }

    $isAdmin = ($user['role'] === 'admin');

    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de l\'annonce requis']);
        exit();
    }

    $id = (int)$_GET['id'];

    // Vérifier que l'annonce existe
    $query = "SELECT userId FROM announcements WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Annonce non trouvée']);
        exit();
    }

    // Vérifier les permissions
    if (!$isAdmin && $announcement['userId'] != $user['id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Vous n\'avez pas la permission de supprimer cette annonce']);
        exit();
    }

    // Supprimer l'annonce
    $query = "DELETE FROM announcements WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Annonce supprimée avec succès'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
    
} catch (Exception $e) {
    error_log("Erreur announcements/delete.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>


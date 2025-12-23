<?php
/**
 * API d'upload d'image pour les annonces
 * POST /api/announcements/upload.php
 * Nécessite authentification (utilisateur inscrit)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../auth/middleware.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    // Vérifier qu'un fichier a été uploadé
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun fichier uploadé ou erreur lors de l\'upload']);
        exit();
    }

    $file = $_FILES['image'];
    
    // Vérifier la taille du fichier (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Le fichier est trop volumineux (max 5MB)']);
        exit();
    }

    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WEBP']);
        exit();
    }

    // Créer le dossier d'upload s'il n'existe pas
    $uploadDir = __DIR__ . '/../uploads/announcements/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Générer un nom de fichier unique
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('announcement_', true) . '_' . time() . '.' . $extension;
    $filePath = $uploadDir . $fileName;

    // Déplacer le fichier
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier']);
        exit();
    }

    // Générer l'URL publique (relative au domaine)
    $publicUrl = '/api/uploads/announcements/' . $fileName;
    
    // Si on est en production, utiliser l'URL complète
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $fullUrl = $protocol . '://' . $host . $publicUrl;

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Image uploadée avec succès',
        'url' => $fullUrl,
        'fileName' => $fileName
    ]);
    
} catch (Exception $e) {
    error_log("Erreur announcements/upload.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>


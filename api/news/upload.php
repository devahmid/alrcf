<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API d'upload d'image pour les actualités
 * POST /api/news/upload.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Aucun fichier uploadé ou erreur lors de l\'upload']);
    exit();
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Vérifier le type de fichier
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Type de fichier non autorisé. Formats acceptés: JPG, PNG, GIF, WEBP']);
    exit();
}

// Vérifier la taille
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux. Taille maximale: 5MB']);
    exit();
}

// Créer le dossier d'upload s'il n'existe pas
$uploadDir = __DIR__ . '/../uploads/news/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Générer un nom de fichier unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('news_', true) . '.' . $extension;
$filepath = $uploadDir . $filename;

// Déplacer le fichier
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Construire l'URL complète de l'image
    // Détecter le protocole (https ou http)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
                 (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                ? 'https' : 'http';
    
    // Détecter le host (peut être dans HTTP_HOST ou SERVER_NAME)
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'alrcf.fr';
    
    // Si on est derrière un proxy, utiliser X-Forwarded-Host
    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    }
    
    $baseUrl = $protocol . '://' . $host;
    $imageUrl = $baseUrl . '/api/uploads/news/' . $filename;
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Image uploadée avec succès',
        'imageUrl' => $imageUrl
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du fichier']);
}
?>


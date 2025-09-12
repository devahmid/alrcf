<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de création d'événement
 * POST /api/events/create.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['title']) || !isset($input['description']) || !isset($input['startDate']) || !isset($input['location'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Titre, description, date de début et lieu requis']);
    exit();
}

$title = $input['title'];
$description = $input['description'];
$startDate = $input['startDate'];
$endDate = $input['endDate'] ?? null;
$location = $input['location'];
$maxParticipants = $input['maxParticipants'] ?? null;
$registrationRequired = $input['registrationRequired'] ?? false;
$registrationDeadline = $input['registrationDeadline'] ?? null;
$isPublished = $input['isPublished'] ?? true;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "INSERT INTO events (title, description, start_date, end_date, location, max_participants, current_participants, registration_required, registration_deadline, is_published, created_at, updated_at) 
              VALUES (:title, :description, :start_date, :end_date, :location, :max_participants, 0, :registration_required, :registration_deadline, :is_published, NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':max_participants', $maxParticipants);
    $stmt->bindParam(':registration_required', $registrationRequired, PDO::PARAM_BOOL);
    $stmt->bindParam(':registration_deadline', $registrationDeadline);
    $stmt->bindParam(':is_published', $isPublished, PDO::PARAM_BOOL);
    
    if ($stmt->execute()) {
        $eventId = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Événement créé avec succès',
            'id' => $eventId
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

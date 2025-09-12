<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de création de cotisation
 * POST /api/subscriptions/create.php
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['adherentId']) || !isset($input['amount']) || !isset($input['paymentDate']) || !isset($input['period'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID adhérent, montant, date de paiement et période requis']);
    exit();
}

$adherentId = $input['adherentId'];
$amount = $input['amount'];
$paymentDate = $input['paymentDate'];
$period = $input['period'];
$status = $input['status'] ?? 'paid';
$paymentMethod = $input['paymentMethod'] ?? 'transfer';
$reference = $input['reference'] ?? null;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $query = "INSERT INTO subscriptions (adherentId, amount, paymentDate, period, status, paymentMethod, reference, createdAt, updatedAt) 
              VALUES (:adherentId, :amount, :paymentDate, :period, :status, :paymentMethod, :reference, NOW(), NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':adherentId', $adherentId);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':paymentDate', $paymentDate);
    $stmt->bindParam(':period', $period);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':paymentMethod', $paymentMethod);
    $stmt->bindParam(':reference', $reference);
    
    if ($stmt->execute()) {
        $subscriptionId = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Cotisation créée avec succès',
            'id' => $subscriptionId
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

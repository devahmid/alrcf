<?php
require_once '../config/database.php';
require_once '../config/cors.php';

/**
 * API de récupération des statistiques
 * GET /api/statistics/get.php
 */

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        throw new Exception('Erreur de connexion à la base de données');
    }
    
    $stats = [];
    
    // Nombre total d'adhérents
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'adherent'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalAdherents'] = $stmt->fetch()['total'];
    
    // Nombre d'adhérents actifs
    $query = "SELECT COUNT(*) as total FROM users WHERE role = 'adherent' AND isActive = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['activeAdherents'] = $stmt->fetch()['total'];
    
    // Nombre d'actualités
    $query = "SELECT COUNT(*) as total FROM news WHERE is_published = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalNews'] = $stmt->fetch()['total'];
    
    // Nombre d'événements
    $query = "SELECT COUNT(*) as total FROM events WHERE is_published = 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalEvents'] = $stmt->fetch()['total'];
    
    // Messages en attente
    $query = "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'new'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['pendingMessages'] = $stmt->fetch()['total'];
    
    // Signalements en attente
    $query = "SELECT COUNT(*) as total FROM reports WHERE status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['pendingReports'] = $stmt->fetch()['total'];
    
    // Total des cotisations
    $query = "SELECT COUNT(*) as total FROM subscriptions";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['totalSubscriptions'] = $stmt->fetch()['total'];
    
    // Revenus du mois en cours
    $query = "SELECT SUM(amount) as total FROM subscriptions 
              WHERE status = 'paid' 
              AND MONTH(paymentDate) = MONTH(CURRENT_DATE()) 
              AND YEAR(paymentDate) = YEAR(CURRENT_DATE())";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    $stats['monthlyRevenue'] = $result['total'] ?? 0;
    
    // Revenus totaux
    $query = "SELECT SUM(amount) as total FROM subscriptions WHERE status = 'paid'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    $stats['totalRevenue'] = $result['total'] ?? 0;
    
    // Adhérents par mois (derniers 12 mois)
    $query = "SELECT 
                DATE_FORMAT(joinDate, '%Y-%m') as month,
                COUNT(*) as count
              FROM users 
              WHERE role = 'adherent' 
              AND joinDate >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 MONTH)
              GROUP BY DATE_FORMAT(joinDate, '%Y-%m')
              ORDER BY month";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['adherentsByMonth'] = $stmt->fetchAll();
    
    // Cotisations par statut
    $query = "SELECT status, COUNT(*) as count FROM subscriptions GROUP BY status";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['subscriptionsByStatus'] = $stmt->fetchAll();
    
    // Signalements par catégorie
    $query = "SELECT category, COUNT(*) as count FROM reports GROUP BY category";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['reportsByCategory'] = $stmt->fetchAll();
    
    http_response_code(200);
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>

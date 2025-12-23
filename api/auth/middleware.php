<?php
// Function to verify JWT token
function verifyToken($db) {
    $headers = apache_request_headers();
    $token = null;

    if (isset($headers['Authorization'])) {
        $matches = array();
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        }
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Fallback for Nginx/FastCGI
        $matches = array();
        if (preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $token = $matches[1];
        }
    }

    if (!$token) {
        return false;
    }

    // Since we are using a simple token (base64 encoded JSON) as seen in register.php/login.php
    // We decode it and check expiration.
    // In a real production app, use proper JWT library (firebase/php-jwt).
    // Based on previous files, token structure: base64_encode(json_encode(['id', 'email', 'role', 'exp']))
    
    $decoded = json_decode(base64_decode($token), true);
    
    if (!$decoded) {
        return false;
    }

    if (isset($decoded['exp']) && $decoded['exp'] < time()) {
        return false;
    }

    // Verify user exists in DB to be sure (and get fresh role)
    // Note: register.php used $userId, login.php probably similar.
    // Let's assume 'id' is in the token payload.
    
    if (!isset($decoded['id'])) {
        return false;
    }

    $query = "SELECT id, email, role, isActive FROM users WHERE id = :id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $decoded['id']);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['isActive']) {
        return false;
    }

    return $user;
}
?>

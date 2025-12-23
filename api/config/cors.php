<?php
/**
 * Configuration CORS pour permettre les requêtes depuis Angular
 */

// Toujours définir les headers CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: false");

// Pour les requêtes OPTIONS (preflight), répondre immédiatement
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Pour les autres requêtes, définir le Content-Type
header("Content-Type: application/json; charset=UTF-8");
?>

<?php
/**
 * Configuration et classe de connexion à la base de données pour ALRCF
 * Un seul fichier pour tout gérer
 */

// Configuration de la base de données
$db_config = [
    'host' => 'localhost', // ou l'IP de votre serveur de base de données
    'dbname' => 'u281164575_alrcf', // Remplacez par le nom de votre base de données
    'username' => 'u281164575_ahmid', // Votre nom d'utilisateur
    'password' => 'Elodie14061990@', // Remplacez par votre mot de passe
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

// Classe de connexion à la base de données
class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    private $options;
    private $pdo;

    public function __construct($config) {
        $this->host = $config['host'];
        $this->dbname = $config['dbname'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->charset = $config['charset'];
        $this->options = $config['options'];
    }

    /**
     * Établir la connexion à la base de données
     * @return PDO|null
     */
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
                $this->pdo = new PDO($dsn, $this->username, $this->password, $this->options);
            } catch (PDOException $e) {
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
                return null;
            }
        }
        return $this->pdo;
    }

    /**
     * Fermer la connexion
     */
    public function closeConnection() {
        $this->pdo = null;
    }

    /**
     * Tester la connexion
     * @return bool
     */
    public function testConnection() {
        $pdo = $this->getConnection();
        if ($pdo === null) {
            return false;
        }
        
        try {
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Fonction pour créer une instance de Database
function createDatabase() {
    global $db_config;
    return new Database($db_config);
}

// Fonction pour obtenir la configuration
function getDatabaseConfig() {
    global $db_config;
    return $db_config;
}
?>
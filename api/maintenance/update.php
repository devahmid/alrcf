<?php
/**
 * Script de mise à jour de l'application
 * Exécuter ce script pour mettre à jour la base de données
 */

require_once '../config/database.php';

class ApplicationUpdate {
    private $db;
    private $version = '1.0.0';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function runUpdate() {
        echo "🔄 Démarrage de la mise à jour de l'application...\n";
        echo "==============================================\n\n";
        
        $this->checkCurrentVersion();
        $this->runMigrations();
        $this->updateVersion();
        
        echo "\n✅ Mise à jour terminée!\n";
    }
    
    private function checkCurrentVersion() {
        echo "🔍 Vérification de la version actuelle...\n";
        
        // Vérifier si la table de version existe
        $query = "SHOW TABLES LIKE 'app_version'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $query = "SELECT version FROM app_version ORDER BY id DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $currentVersion = $stmt->fetchColumn();
            echo "📋 Version actuelle: $currentVersion\n";
        } else {
            echo "📋 Aucune version enregistrée\n";
        }
        
        echo "📋 Version cible: $this->version\n\n";
    }
    
    private function runMigrations() {
        echo "🚀 Exécution des migrations...\n";
        
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration) {
            echo "  - $migration... ";
            
            try {
                $this->executeMigration($migration);
                echo "✅\n";
            } catch (Exception $e) {
                echo "❌ Erreur: " . $e->getMessage() . "\n";
            }
        }
    }
    
    private function getMigrations() {
        return [
            'create_app_version_table',
            'add_indexes',
            'update_data_types',
            'add_new_columns'
        ];
    }
    
    private function executeMigration($migration) {
        switch ($migration) {
            case 'create_app_version_table':
                $this->createAppVersionTable();
                break;
            case 'add_indexes':
                $this->addIndexes();
                break;
            case 'update_data_types':
                $this->updateDataTypes();
                break;
            case 'add_new_columns':
                $this->addNewColumns();
                break;
        }
    }
    
    private function createAppVersionTable() {
        $query = "CREATE TABLE IF NOT EXISTS app_version (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(20) NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($query);
    }
    
    private function addIndexes() {
        $indexes = [
            "CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)",
            "CREATE INDEX IF NOT EXISTS idx_users_role ON users(role)",
            "CREATE INDEX IF NOT EXISTS idx_news_published ON news(is_published, published_at)",
            "CREATE INDEX IF NOT EXISTS idx_events_startDate ON events(start_date)",
            "CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages(status)",
            "CREATE INDEX IF NOT EXISTS idx_reports_adherent ON reports(adherent_id)",
            "CREATE INDEX IF NOT EXISTS idx_reports_status ON reports(status)",
            "CREATE INDEX IF NOT EXISTS idx_subscriptions_adherent ON subscriptions(adherent_id)",
            "CREATE INDEX IF NOT EXISTS idx_subscriptions_period ON subscriptions(period)"
        ];
        
        foreach ($indexes as $index) {
            $this->db->exec($index);
        }
    }
    
    private function updateDataTypes() {
        // Mettre à jour les types de données si nécessaire
        $updates = [
            "ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NOT NULL",
            "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL",
            "ALTER TABLE news MODIFY COLUMN title VARCHAR(255) NOT NULL",
            "ALTER TABLE events MODIFY COLUMN title VARCHAR(255) NOT NULL"
        ];
        
        foreach ($updates as $update) {
            try {
                $this->db->exec($update);
            } catch (Exception $e) {
                // Ignorer les erreurs si la colonne n'existe pas
            }
        }
    }
    
    private function addNewColumns() {
        // Ajouter de nouvelles colonnes si nécessaire
        $columns = [
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        foreach ($columns as $column) {
            try {
                $this->db->exec($column);
            } catch (Exception $e) {
                // Ignorer les erreurs si la colonne existe déjà
            }
        }
    }
    
    private function updateVersion() {
        echo "📝 Mise à jour de la version...\n";
        
        $query = "INSERT INTO app_version (version) VALUES (?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->version]);
        
        echo "✅ Version mise à jour vers $this->version\n";
    }
}

// Exécuter la mise à jour
$update = new ApplicationUpdate();
$update->runUpdate();
?>

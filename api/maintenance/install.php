<?php
/**
 * Script d'installation complet de l'application
 * Exécuter ce script pour installer l'application ALRCF
 */

require_once '../config/database.php';

class ApplicationInstaller {
    private $db;
    private $version = '1.0.0';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function install() {
        echo "🚀 Installation de l'application ALRCF...\n";
        echo "=====================================\n\n";
        
        $this->checkRequirements();
        $this->createTables();
        $this->insertDefaultData();
        $this->createDirectories();
        $this->setPermissions();
        $this->createConfigFile();
        
        echo "\n✅ Installation terminée!\n";
        echo "🌐 Application disponible sur: http://localhost:4200\n";
        echo "🔧 API disponible sur: http://localhost:8000\n";
        echo "📊 Base de données: http://localhost:8000/database/install.php\n";
    }
    
    private function checkRequirements() {
        echo "🔍 Vérification des prérequis...\n";
        
        $requirements = [
            'PHP Version' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO MySQL' => extension_loaded('pdo_mysql'),
            'JSON Extension' => extension_loaded('json'),
            'CURL Extension' => extension_loaded('curl')
        ];
        
        $allOk = true;
        foreach ($requirements as $requirement => $status) {
            if ($status) {
                echo "  ✅ $requirement\n";
            } else {
                echo "  ❌ $requirement\n";
                $allOk = false;
            }
        }
        
        if (!$allOk) {
            echo "\n❌ Certains prérequis ne sont pas satisfaits.\n";
            exit(1);
        }
        
        echo "✅ Tous les prérequis sont satisfaits!\n\n";
    }
    
    private function createTables() {
        echo "🗄️  Création des tables...\n";
        
        $sql = file_get_contents('../database/schema.sql');
        $queries = explode(';', $sql);
        
        $created = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $this->db->exec($query);
                    $created++;
                } catch (Exception $e) {
                    echo "  ⚠️  Erreur: " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "✅ $created requêtes exécutées\n";
    }
    
    private function insertDefaultData() {
        echo "📊 Insertion des données par défaut...\n";
        
        // Vérifier si des données existent déjà
        $query = "SELECT COUNT(*) FROM users";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $userCount = $stmt->fetchColumn();
        
        if ($userCount > 0) {
            echo "  ℹ️  Des données existent déjà, insertion ignorée\n";
            return;
        }
        
        // Insérer l'admin par défaut
        $query = "INSERT INTO users (
            email, password, firstName, lastName, role, isActive, 
            memberNumber, joinDate, subscriptionStatus, subscriptionAmount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'admin@alrcf.fr',
            password_hash('password', PASSWORD_DEFAULT),
            'Administrateur',
            'ALRCF',
            'admin',
            1,
            'ADMIN001',
            date('Y-m-d'),
            'active',
            0.00
        ]);
        
        echo "✅ Utilisateur admin créé\n";
    }
    
    private function createDirectories() {
        echo "📁 Création des dossiers...\n";
        
        $directories = [
            '../logs',
            '../uploads',
            '../uploads/temp',
            '../backups',
            '../cache'
        ];
        
        $created = 0;
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (mkdir($dir, 0755, true)) {
                    $created++;
                }
            }
        }
        
        echo "✅ $created dossiers créés\n";
    }
    
    private function setPermissions() {
        echo "🔐 Configuration des permissions...\n";
        
        $directories = [
            '../logs',
            '../uploads',
            '../backups',
            '../cache'
        ];
        
        $set = 0;
        foreach ($directories as $dir) {
            if (file_exists($dir)) {
                chmod($dir, 0755);
                $set++;
            }
        }
        
        echo "✅ Permissions configurées pour $set dossiers\n";
    }
    
    private function createConfigFile() {
        echo "⚙️  Création du fichier de configuration...\n";
        
        $config = [
            'app_name' => 'ALRCF Association',
            'version' => $this->version,
            'installed_at' => date('Y-m-d H:i:s'),
            'admin_email' => 'admin@alrcf.fr',
            'admin_password' => 'password'
        ];
        
        $configFile = '../config/app.json';
        if (file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT))) {
            echo "✅ Fichier de configuration créé\n";
        } else {
            echo "⚠️  Impossible de créer le fichier de configuration\n";
        }
    }
}

// Exécuter l'installation
$installer = new ApplicationInstaller();
$installer->install();
?>

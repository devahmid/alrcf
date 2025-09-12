<?php
/**
 * Script de nettoyage de la base de données
 * Exécuter ce script pour nettoyer les données obsolètes
 */

require_once '../config/database.php';

class DatabaseCleanup {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function runCleanup() {
        echo "🧹 Démarrage du nettoyage de la base de données...\n";
        echo "================================================\n\n";
        
        $this->cleanupExpiredSessions();
        $this->cleanupOldLogs();
        $this->cleanupTemporaryFiles();
        $this->optimizeTables();
        
        echo "\n✅ Nettoyage terminé!\n";
    }
    
    private function cleanupExpiredSessions() {
        echo "🔐 Nettoyage des sessions expirées...\n";
        
        // Supprimer les sessions expirées (plus de 30 jours)
        $query = "DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $deleted = $stmt->rowCount();
        echo "✅ $deleted sessions expirées supprimées\n";
    }
    
    private function cleanupOldLogs() {
        echo "📝 Nettoyage des anciens logs...\n";
        
        // Supprimer les logs de plus de 90 jours
        $query = "DELETE FROM logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $deleted = $stmt->rowCount();
        echo "✅ $deleted entrées de log supprimées\n";
    }
    
    private function cleanupTemporaryFiles() {
        echo "🗂️  Nettoyage des fichiers temporaires...\n";
        
        $tempDir = '../uploads/temp/';
        $deleted = 0;
        
        if (is_dir($tempDir)) {
            $files = glob($tempDir . '*');
            $now = time();
            
            foreach ($files as $file) {
                if (is_file($file) && ($now - filemtime($file)) > 3600) { // 1 heure
                    unlink($file);
                    $deleted++;
                }
            }
        }
        
        echo "✅ $deleted fichiers temporaires supprimés\n";
    }
    
    private function optimizeTables() {
        echo "⚡ Optimisation des tables...\n";
        
        $tables = $this->getTables();
        $optimized = 0;
        
        foreach ($tables as $table) {
            $query = "OPTIMIZE TABLE `$table`";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $optimized++;
        }
        
        echo "✅ $optimized tables optimisées\n";
    }
    
    private function getTables() {
        $query = "SHOW TABLES";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        return $tables;
    }
}

// Exécuter le nettoyage
$cleanup = new DatabaseCleanup();
$cleanup->runCleanup();
?>

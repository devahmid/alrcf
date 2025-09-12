<?php
/**
 * Script de sauvegarde de la base de données
 * Exécuter ce script pour créer une sauvegarde complète
 */

require_once '../config/database.php';

class DatabaseBackup {
    private $db;
    private $backupDir = '../backups/';
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Créer le dossier de sauvegarde s'il n'existe pas
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    public function createBackup() {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "alrcf_backup_$timestamp.sql";
        $filepath = $this->backupDir . $filename;
        
        echo "🔄 Création de la sauvegarde...\n";
        
        // Obtenir la liste des tables
        $tables = $this->getTables();
        
        $sql = "-- Sauvegarde ALRCF Association\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Version: 1.0.0\n\n";
        
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $sql .= $this->getTableStructure($table);
            $sql .= $this->getTableData($table);
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        // Écrire le fichier
        if (file_put_contents($filepath, $sql)) {
            echo "✅ Sauvegarde créée: $filename\n";
            echo "📁 Emplacement: $filepath\n";
            echo "📊 Taille: " . $this->formatBytes(filesize($filepath)) . "\n";
            
            // Nettoyer les anciennes sauvegardes (garder les 10 dernières)
            $this->cleanupOldBackups();
            
            return $filename;
        } else {
            echo "❌ Erreur lors de la création de la sauvegarde\n";
            return false;
        }
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
    
    private function getTableStructure($table) {
        $sql = "-- Structure de la table `$table`\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";
        
        $query = "SHOW CREATE TABLE `$table`";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $sql .= $row[1] . ";\n\n";
        
        return $sql;
    }
    
    private function getTableData($table) {
        $sql = "-- Données de la table `$table`\n";
        
        $query = "SELECT * FROM `$table`";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $sql .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values = [];
            foreach ($rows as $row) {
                $rowValues = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = "(" . implode(', ', $rowValues) . ")";
            }
            
            $sql .= implode(",\n", $values) . ";\n\n";
        }
        
        return $sql;
    }
    
    private function cleanupOldBackups() {
        $files = glob($this->backupDir . "alrcf_backup_*.sql");
        
        if (count($files) > 10) {
            // Trier par date de modification (plus ancien en premier)
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            // Supprimer les anciens fichiers
            $filesToDelete = array_slice($files, 0, count($files) - 10);
            foreach ($filesToDelete as $file) {
                unlink($file);
                echo "🗑️  Ancienne sauvegarde supprimée: " . basename($file) . "\n";
            }
        }
    }
    
    private function formatBytes($size, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}

// Exécuter la sauvegarde
$backup = new DatabaseBackup();
$backup->createBackup();
?>

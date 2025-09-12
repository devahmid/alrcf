<?php
/**
 * Script d'installation de la base de données
 * Exécuter ce script pour créer la base de données et les tables
 */

require_once '../config/database.php';

try {
    // Connexion sans spécifier de base de données
    $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Lire le fichier SQL
    $sql = file_get_contents('schema.sql');
    
    // Diviser les requêtes par point-virgule
    $queries = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                $successCount++;
            } catch (PDOException $e) {
                $errorCount++;
                echo "Erreur lors de l'exécution de la requête: " . $e->getMessage() . "\n";
                echo "Requête: " . substr($query, 0, 100) . "...\n\n";
            }
        }
    }
    
    echo "Installation terminée!\n";
    echo "Requêtes exécutées avec succès: $successCount\n";
    echo "Erreurs: $errorCount\n\n";
    
    if ($errorCount == 0) {
        echo "✅ Base de données créée avec succès!\n";
        echo "📧 Compte admin créé: admin@alrcf.fr\n";
        echo "🔑 Mot de passe: password\n";
        echo "🌐 Vous pouvez maintenant utiliser l'application!\n";
    } else {
        echo "⚠️  Installation terminée avec des erreurs. Vérifiez les messages ci-dessus.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à MySQL: " . $e->getMessage() . "\n";
    echo "Vérifiez que MySQL est démarré et que les identifiants sont corrects.\n";
}
?>

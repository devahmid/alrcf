<?php
/**
 * Tests basiques de l'API ALRCF
 * Exécuter ce script pour tester les endpoints principaux
 */

require_once 'config/database.php';

class ApiTester {
    private $baseUrl = 'http://localhost:8000';
    private $testResults = [];
    
    public function runTests() {
        echo "🧪 Démarrage des tests de l'API ALRCF\n";
        echo "=====================================\n\n";
        
        $this->testApiHealth();
        $this->testNewsEndpoint();
        $this->testEventsEndpoint();
        $this->testContactEndpoint();
        $this->testDatabaseConnection();
        
        $this->displayResults();
    }
    
    private function testApiHealth() {
        echo "🔍 Test de santé de l'API...\n";
        
        $response = $this->makeRequest('/');
        if ($response && isset($response['message'])) {
            $this->testResults['api_health'] = '✅ PASS';
            echo "✅ API accessible\n";
        } else {
            $this->testResults['api_health'] = '❌ FAIL';
            echo "❌ API non accessible\n";
        }
        echo "\n";
    }
    
    private function testNewsEndpoint() {
        echo "📰 Test de l'endpoint actualités...\n";
        
        $response = $this->makeRequest('/news/get.php');
        if ($response && is_array($response)) {
            $this->testResults['news_endpoint'] = '✅ PASS';
            echo "✅ Endpoint actualités fonctionnel\n";
        } else {
            $this->testResults['news_endpoint'] = '❌ FAIL';
            echo "❌ Endpoint actualités défaillant\n";
        }
        echo "\n";
    }
    
    private function testEventsEndpoint() {
        echo "📅 Test de l'endpoint événements...\n";
        
        $response = $this->makeRequest('/events/get.php');
        if ($response && is_array($response)) {
            $this->testResults['events_endpoint'] = '✅ PASS';
            echo "✅ Endpoint événements fonctionnel\n";
        } else {
            $this->testResults['events_endpoint'] = '❌ FAIL';
            echo "❌ Endpoint événements défaillant\n";
        }
        echo "\n";
    }
    
    private function testContactEndpoint() {
        echo "📧 Test de l'endpoint contact...\n";
        
        $testData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];
        
        $response = $this->makeRequest('/contact/send.php', 'POST', $testData);
        if ($response && isset($response['success']) && $response['success']) {
            $this->testResults['contact_endpoint'] = '✅ PASS';
            echo "✅ Endpoint contact fonctionnel\n";
        } else {
            $this->testResults['contact_endpoint'] = '❌ FAIL';
            echo "❌ Endpoint contact défaillant\n";
        }
        echo "\n";
    }
    
    private function testDatabaseConnection() {
        echo "🗄️  Test de connexion à la base de données...\n";
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            if ($db) {
                $this->testResults['database_connection'] = '✅ PASS';
                echo "✅ Base de données accessible\n";
            } else {
                $this->testResults['database_connection'] = '❌ FAIL';
                echo "❌ Base de données non accessible\n";
            }
        } catch (Exception $e) {
            $this->testResults['database_connection'] = '❌ FAIL';
            echo "❌ Erreur de base de données: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen(json_encode($data))
                ]);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false) {
            return false;
        }
        
        $decodedResponse = json_decode($response, true);
        return $decodedResponse !== null ? $decodedResponse : $response;
    }
    
    private function displayResults() {
        echo "📊 Résultats des tests\n";
        echo "=====================\n";
        
        foreach ($this->testResults as $test => $result) {
            echo "$test: $result\n";
        }
        
        $passed = count(array_filter($this->testResults, function($result) {
            return strpos($result, '✅') !== false;
        }));
        
        $total = count($this->testResults);
        
        echo "\n";
        echo "Résumé: $passed/$total tests réussis\n";
        
        if ($passed === $total) {
            echo "🎉 Tous les tests sont passés!\n";
        } else {
            echo "⚠️  Certains tests ont échoué. Vérifiez la configuration.\n";
        }
    }
}

// Exécuter les tests
$tester = new ApiTester();
$tester->runTests();
?>

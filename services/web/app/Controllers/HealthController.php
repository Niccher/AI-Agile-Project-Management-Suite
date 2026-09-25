<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class HealthController extends Controller
{
    public function index()
    {
        $dbConnected = false;
        try {
            $db = Database::connect();
            $db->query("SELECT 1");
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        $mlHealth = null;
        $mlServiceUrl = rtrim(env('ML_SERVICE_URL', 'http://ml-chege-jira:8000'), '/');
        
        try {
            $client = \Config\Services::curlrequest();
            $response = $client->request('GET', $mlServiceUrl . '/api/v1/health', [
                'timeout' => 3
            ]);
            $mlHealth = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $mlHealth = ['success' => false, 'error' => $e->getMessage()];
        }

        $healthStatus = [
            'webapp' => [
                'status' => 'healthy',
                'database_connected' => $dbConnected
            ],
            'ml_service' => $mlHealth,
            'timestamp' => time()
        ];

        return $this->response->setJSON($healthStatus);
    }
}

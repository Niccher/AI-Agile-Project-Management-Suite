<?php

namespace App\Controllers;

use App\Session\Handlers\ResilientSessionHandler;
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

        // 1. Resilience Telemetry (50ms non-blocking probe)
        $resilience = ResilientSessionHandler::getResilienceStatus();

        // 2. ML Service Health
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

        // Determine WebApp status
        $webappStatus = 'healthy';
        if (!$dbConnected) {
            $webappStatus = 'critical';
        } elseif ($resilience['fallback_active']) {
            $webappStatus = 'healthy_degraded';
        }

        $healthStatus = [
            'webapp' => [
                'status'             => $webappStatus,
                'database_connected' => $dbConnected,
                'active_engine'      => $resilience['session_engine'],
            ],
            'resilience' => $resilience,
            'ml_service' => $mlHealth,
            'timestamp'  => time()
        ];

        $statusCode = (!$dbConnected) ? 503 : 200;
        return $this->response->setStatusCode($statusCode)->setJSON($healthStatus);
    }
}

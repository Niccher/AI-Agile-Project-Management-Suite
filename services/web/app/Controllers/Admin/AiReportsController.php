<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AiReportsController extends BaseController
{
    public function index()
    {
        // Just render a view where they can select a Sprint
        return view('admin/ai/reports');
    }

    public function generate()
    {
        // Receive sprint_id or custom prompt from request
        $prompt = $this->request->getPost('prompt');
        $model = $this->request->getPost('model') ?? 'mistral-7b';

        if (empty($prompt)) {
            return $this->response->setJSON(['success' => false, 'error' => 'Prompt is required.']);
        }

        // Call the ML app Async Task endpoint
        $mlServiceUrl = setting('App.mlServiceUrl') ?? 'http://ml-chege-jira:8000';
        $apiKey = setting('App.mlApiKey') ?? 'chege_jira_ml_super_secret_key_2026';

        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->post($mlServiceUrl . '/api/v1/async-tasks/generate', [
                'headers' => [
                    'Accept' => 'application/json',
                    'X-API-Key' => $apiKey,
                ],
                'json' => [
                    'prompt' => $prompt,
                    'model' => $model,
                    'max_tokens' => 2048,
                    'temperature' => 0.4,
                    'system_prompt' => "You are an expert Agile Scrum Master and Technical Writer. Generate a comprehensive and professional Markdown report."
                ],
                'http_errors' => false
            ]);

            $body = json_decode($response->getBody(), true);
            
            if ($response->getStatusCode() === 202) {
                return $this->response->setJSON([
                    'success' => true,
                    'task_id' => $body['task_id'],
                    'message' => 'Task queued successfully'
                ]);
            }

            return $this->response->setJSON(['success' => false, 'error' => 'Failed to start task.', 'details' => $body]);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function status($taskId)
    {
        $mlServiceUrl = setting('App.mlServiceUrl') ?? 'http://ml-chege-jira:8000';
        $apiKey = setting('App.mlApiKey') ?? 'chege_jira_ml_super_secret_key_2026';

        $client = \Config\Services::curlrequest();
        
        try {
            $response = $client->get($mlServiceUrl . '/api/v1/async-tasks/' . $taskId, [
                'headers' => [
                    'Accept' => 'application/json',
                    'X-API-Key' => $apiKey,
                ],
                'http_errors' => false
            ]);

            $body = json_decode($response->getBody(), true);
            return $this->response->setJSON($body);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

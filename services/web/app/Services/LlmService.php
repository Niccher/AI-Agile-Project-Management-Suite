<?php

namespace App\Services;

/**
 * Service class wrapping cURL requests to the Python FastAPI ML backend.
 */
class LlmService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? setting('Ml.serviceUrl') ?? env('ML_SERVICE_URL', 'http://ml-chege-jira:8000'), '/');
        $this->apiKey  = $apiKey ?? setting('Ml.apiKey') ?? env('ML_API_KEY', 'chege_jira_ml_super_secret_key_2026');
    }

    /**
     * Check backend health and model availability
     */
    public function getHealth(): array
    {
        return $this->request('GET', '/api/v1/health');
    }

    /**
     * Get system and LLM runtime telemetry
     */
    public function getTelemetry(): array
    {
        return $this->request('GET', '/api/v1/admin/telemetry');
    }

    /**
     * Get list of models
     */
    public function getModels(): array
    {
        return $this->request('GET', '/api/v1/models');
    }

    /**
     * Get current runtime config
     */
    public function getConfig(): array
    {
        return $this->request('GET', '/api/v1/admin/config');
    }

    /**
     * Update runtime configuration
     */
    public function updateConfig(array $data): array
    {
        return $this->request('PATCH', '/api/v1/admin/config', $data);
    }

    /**
     * Trigger background download of a GGUF model
     */
    public function downloadModel(string $modelKey): array
    {
        return $this->request('POST', "/api/v1/admin/models/{$modelKey}/download");
    }

    /**
     * Pre-load model into cache
     */
    public function preloadModel(string $modelKey): array
    {
        return $this->request('POST', "/api/v1/admin/models/{$modelKey}/cache");
    }

    /**
     * Reload model in RAM cache
     */
    public function reloadModel(string $modelKey): array
    {
        return $this->request('POST', "/api/v1/admin/models/{$modelKey}/reload");
    }

    /**
     * Evict model from RAM cache
     */
    public function evictModel(string $modelKey): array
    {
        return $this->request('DELETE', "/api/v1/admin/models/{$modelKey}/cache");
    }

    /**
     * Trigger task ticket enhancement
     */
    public function enhanceTask(int $taskId, ?string $model = null): array
    {
        $payload = [];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', "/api/v1/tasks/{$taskId}/enhancements", $payload);
    }

    /**
     * Suggest priority and story points for a task
     */
    public function suggestPriority(int $taskId, ?string $model = null): array
    {
        $payload = [];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', "/api/v1/tasks/{$taskId}/priority-suggestions", $payload);
    }

    /**
     * Generate sprint health summary
     */
    public function summariseSprint(int $sprintId, ?string $model = null): array
    {
        $payload = [];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', "/api/v1/sprints/{$sprintId}/summaries", $payload);
    }

    /**
     * Generate project wiki documentation page
     */
    public function generateWikiPage(int $projectId, string $title, ?int $parentId = null, ?int $createdBy = null, ?string $model = null): array
    {
        $payload = [
            'page_title' => $title,
            'parent_id'  => $parentId,
            'created_by' => $createdBy,
        ];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', "/api/v1/projects/{$projectId}/wiki-pages", $payload);
    }

    /**
     * Generate developer time productivity report
     */
    public function generateTimeReport(int $userId, ?int $projectId = null, ?string $from = null, ?string $to = null, ?string $model = null): array
    {
        $payload = [
            'user_id'     => $userId,
            'project_id'  => $projectId,
            'period_from' => $from,
            'period_to'   => $to,
        ];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', '/api/v1/time-reports', $payload);
    }

    /**
     * Execute Q&A query
     */
    public function ask(string $question, ?array $context = null, ?string $model = null): array
    {
        $payload = [
            'question' => $question,
            'context'  => $context,
        ];
        if ($model) {
            $payload['model'] = $model;
        }
        return $this->request('POST', '/api/v1/qa', $payload);
    }

    /**
     * Generic HTTP dispatcher using CI4 curlrequest service
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;
        $client = \Config\Services::curlrequest([
            'timeout'     => 180, // CPU LLM inference can take time
            'http_errors' => false,
        ]);

        $options = [
            'headers' => [
                'X-API-Key'    => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ];

        if (in_array(strtoupper($method), ['POST', 'PATCH', 'PUT']) && !empty($data)) {
            $options['body'] = json_encode($data);
        }

        try {
            $response = $client->request($method, $url, $options);
            $rawBody = (string)$response->getBody();
            $decoded = json_decode($rawBody, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            return [
                'success' => false,
                'error'   => [
                    'code'    => 'invalid_json_response',
                    'message' => 'ML backend did not return valid JSON: ' . substr($rawBody, 0, 200),
                ],
            ];
        } catch (\Throwable $e) {
            log_message('error', "LlmService Exception on {$method} {$url}: " . $e->getMessage());
            return [
                'success' => false,
                'error'   => [
                    'code'    => 'service_unreachable',
                    'message' => 'Could not communicate with ML microservice: ' . $e->getMessage(),
                ],
            ];
        }
    }
}

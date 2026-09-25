<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\LlmService;

class AiController extends BaseController
{
    protected LlmService $llm;

    public function __construct()
    {
        $this->llm = new LlmService();
    }

    /**
     * AI Microservice Telemetry & Cache Monitor
     */
    public function telemetry()
    {
        $telemetryData = $this->llm->getTelemetry();
        $modelsData    = $this->llm->getModels();

        return view('admin/ai/telemetry', [
            'telemetry' => $telemetryData['data'] ?? [],
            'models'    => $modelsData['data']['models'] ?? $this->getDefaultModelsCatalog(),
            'isOnline'  => $telemetryData['success'] ?? false,
            'errorMsg'  => $telemetryData['error']['message'] ?? null,
        ]);
    }

    /**
     * AI Runtime Configuration & Model Management
     */
    public function settings()
    {
        $serviceUrl = setting('Ml.serviceUrl') ?? env('ML_SERVICE_URL', 'http://ml-chege-jira:8000');
        $apiKey     = setting('Ml.apiKey') ?? env('ML_API_KEY', 'chege_jira_ml_super_secret_key_2026');

        $configData = $this->llm->getConfig();
        $modelsData = $this->llm->getModels();

        $modelsList = $modelsData['data']['models'] ?? $this->getDefaultModelsCatalog();

        return view('admin/ai/settings', [
            'serviceUrl' => $serviceUrl,
            'apiKey'     => $apiKey,
            'config'     => $configData['data'] ?? [
                'default_model' => 'phi3-mini',
                'n_gpu_layers'  => 0,
                'n_threads'     => 4,
                'n_ctx'         => 4096,
            ],
            'models'     => $modelsList,
            'isOnline'   => $configData['success'] ?? false,
            'errorMsg'   => $configData['error']['message'] ?? null,
        ]);
    }

    /**
     * AJAX endpoint returning live model status and download progress
     */
    public function modelsJson()
    {
        $modelsData = $this->llm->getModels();
        if (!empty($modelsData['success'])) {
            return $this->response->setJSON($modelsData['data']);
        }

        return $this->response->setJSON([
            'models' => $this->getDefaultModelsCatalog(),
            'online' => false,
        ]);
    }

    /**
     * Save AI Settings: Service URL, Port, API Key, and Model Parameters
     */
    public function updateSettings()
    {
        $serviceUrl   = trim((string)$this->request->getPost('service_url'));
        $apiKey       = trim((string)$this->request->getPost('api_key'));
        $defaultModel = trim((string)$this->request->getPost('default_model'));
        $computeMode  = trim((string)$this->request->getPost('compute_mode'));
        $nThreads     = (int)($this->request->getPost('n_threads') ?? 4);
        $nCtx         = (int)($this->request->getPost('n_ctx') ?? 4096);

        // 1. Save Connection Parameters to WebApp Settings
        if (!empty($serviceUrl)) {
            setting('Ml.serviceUrl', rtrim($serviceUrl, '/'));
        }
        if (!empty($apiKey)) {
            setting('Ml.apiKey', $apiKey);
        }

        // 2. Instantiate updated LLM client
        $updatedLlm = new LlmService($serviceUrl, $apiKey);

        // 3. Dispatch runtime configuration to ML microservice
        $n_gpu_layers = ($computeMode === 'gpu') ? -1 : 0;
        $res = $updatedLlm->updateConfig([
            'default_model' => $defaultModel ?: 'phi3-mini',
            'n_gpu_layers'  => $n_gpu_layers,
            'n_threads'     => max(1, min(64, $nThreads)),
            'n_ctx'         => max(512, min(32768, $nCtx)),
        ]);

        if (!empty($res['success'])) {
            return redirect()->to(site_url('admin/ai/settings'))->with('message', 'AI Service URL and runtime parameters updated and synchronized successfully.');
        }

        $warning = 'Saved connection settings locally, but could not sync parameters with ML backend at ' . esc($serviceUrl) . ' (' . ($res['error']['message'] ?? 'Service unreachable') . ')';
        return redirect()->to(site_url('admin/ai/settings'))->with('message', $warning);
    }

    /**
     * Test connection to ML Microservice
     */
    public function testConnection()
    {
        $serviceUrl = trim((string)$this->request->getPost('service_url'));
        $apiKey     = trim((string)$this->request->getPost('api_key'));

        $testLlm = new LlmService($serviceUrl, $apiKey);
        $health = $testLlm->getHealth();

        if (!empty($health['success'])) {
            return redirect()->to(site_url('admin/ai/settings'))->with('message', 'Connection successful! ML Microservice is healthy and reachable at ' . esc($serviceUrl));
        }

        $errMsg = $health['error']['message'] ?? 'Could not reach service';
        return redirect()->to(site_url('admin/ai/settings'))->with('error', 'Connection failed to ' . esc($serviceUrl) . ': ' . $errMsg);
    }

    /**
     * Model & Cache Action (download, preload, reload, evict)
     */
    public function cacheAction()
    {
        $action   = trim((string)$this->request->getPost('action'));
        $modelKey = trim((string)$this->request->getPost('model_key'));
        $redirect = trim((string)$this->request->getPost('redirect')) ?: 'settings';

        $targetUrl = ($redirect === 'telemetry') ? 'admin/ai/telemetry' : 'admin/ai/settings';

        if ($action === 'download') {
            $res = $this->llm->downloadModel($modelKey);
        } elseif ($action === 'preload') {
            $res = $this->llm->preloadModel($modelKey);
        } elseif ($action === 'reload') {
            $res = $this->llm->reloadModel($modelKey);
        } elseif ($action === 'evict') {
            $res = $this->llm->evictModel($modelKey);
        } else {
            return redirect()->to(site_url($targetUrl))->with('error', 'Invalid model action.');
        }

        if (!empty($res['success'])) {
            return redirect()->to(site_url($targetUrl))->with('message', $res['data']['message'] ?? 'Model operation initiated successfully.');
        }

        $msg = $res['error']['message'] ?? 'Model operation failed.';
        return redirect()->to(site_url($targetUrl))->with('error', $msg);
    }

    /**
     * Default model catalog fallback when microservice is cold/starting
     */
    protected function getDefaultModelsCatalog(): array
    {
        return [
            'phi3-mini' => [
                'key'                => 'phi3-mini',
                'name'               => 'Phi-3 Mini 4K Instruct',
                'provider'           => 'Microsoft',
                'params'             => '3.8B',
                'quant'              => 'Q4_K_M',
                'approx_size_gb'     => 2.2,
                'recommended_ram_gb' => 2.5,
                'best_for'           => 'Ultra-fast CPU inference, lightweight tasks, priority estimation, time log summaries',
                'badge_color'        => 'success',
                'file'               => 'Phi-3-mini-4k-instruct.Q4_K_M.gguf',
                'exists_on_disk'     => false,
                'loaded_in_ram'      => false,
                'total_requests'     => 0,
                'avg_duration_ms'    => null,
                'download_status'    => 'idle',
                'download_progress_pct' => 0,
            ],
            'mistral-7b' => [
                'key'                => 'mistral-7b',
                'name'               => 'Mistral 7B Instruct v0.2',
                'provider'           => 'Mistral AI',
                'params'             => '7.3B',
                'quant'              => 'Q4_K_M',
                'approx_size_gb'     => 4.1,
                'recommended_ram_gb' => 4.5,
                'best_for'           => 'Best all-round performance for Jira ticket expansion, agile summaries, and Q&A',
                'badge_color'        => 'primary',
                'file'               => 'mistral-7b-instruct-v0.2.Q4_K_M.gguf',
                'exists_on_disk'     => false,
                'loaded_in_ram'      => false,
                'total_requests'     => 0,
                'avg_duration_ms'    => null,
                'download_status'    => 'idle',
                'download_progress_pct' => 0,
            ],
            'llama3-8b' => [
                'key'                => 'llama3-8b',
                'name'               => 'Meta Llama 3 8B Instruct',
                'provider'           => 'Meta AI',
                'params'             => '8.0B',
                'quant'              => 'Q4_K_M',
                'approx_size_gb'     => 4.9,
                'recommended_ram_gb' => 5.2,
                'best_for'           => 'Advanced architectural reasoning, full wiki documentation drafting, complex sprint analysis',
                'badge_color'        => 'info',
                'file'               => 'Meta-Llama-3-8B-Instruct.Q4_K_M.gguf',
                'exists_on_disk'     => false,
                'loaded_in_ram'      => false,
                'total_requests'     => 0,
                'avg_duration_ms'    => null,
                'download_status'    => 'idle',
                'download_progress_pct' => 0,
            ],
            'deepseek-7b' => [
                'key'                => 'deepseek-7b',
                'name'               => 'DeepSeek Coder 7B Instruct v1.5',
                'provider'           => 'DeepSeek',
                'params'             => '7.0B',
                'quant'              => 'Q4_K_M',
                'approx_size_gb'     => 4.1,
                'recommended_ram_gb' => 4.5,
                'best_for'           => 'Deep code understanding, technical stack analysis, and system architecture docs',
                'badge_color'        => 'warning',
                'file'               => 'deepseek-coder-7b-instruct.Q4_K_M.gguf',
                'exists_on_disk'     => false,
                'loaded_in_ram'      => false,
                'total_requests'     => 0,
                'avg_duration_ms'    => null,
                'download_status'    => 'idle',
                'download_progress_pct' => 0,
            ],
        ];
    }
}

<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        $reportModel = new \App\Models\ReportModel();
        $reports = $reportModel->orderBy('created_at', 'DESC')->findAll(10);
        
        return view('manager/reports/index', ['reports' => $reports]);
    }

    public function generate()
    {
        try {
            $type = $this->request->getPost('type') ?? 'pdf';
            $startInput = trim((string)$this->request->getPost('period_start'));
            $endInput = trim((string)$this->request->getPost('period_end'));

            $start = !empty($startInput) ? $startInput : null;
            $end = !empty($endInput) ? $endInput : null;

            $db = \Config\Database::connect();
            $hasFirstName = $db->fieldExists('first_name', 'users');
            $userCols = $hasFirstName ? 'users.username, users.first_name, users.last_name' : 'users.username';

            $builder = $db->table('tasks')
                ->select("projects.name as project_name, tasks.title as task_title, {$userCols}, tasks.status, COALESCE(SUM(time_logs.duration)/3600, 0) as logged_hours")
                ->join('projects', 'projects.id = tasks.project_id', 'left')
                ->join('users', 'users.id = tasks.assigned_to', 'left')
                ->join('time_logs', 'time_logs.project_id = tasks.project_id AND time_logs.task_name = tasks.title', 'left')
                ->groupBy('tasks.id');

            if (!empty($start)) {
                $builder->where('tasks.created_at >=', $start . ' 00:00:00');
            }
            if (!empty($end)) {
                $builder->where('tasks.created_at <=', $end . ' 23:59:59');
            }

            $rows = $builder->get()->getResultArray();

            $headers = ['Project', 'Task', 'Assignee', 'Status', 'Logged Time (hrs)'];
            $data = [];
            foreach ($rows as $row) {
                $assignee = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
                if (empty($assignee)) {
                    $assignee = $row['username'] ?? 'Unassigned';
                }
                $data[] = [
                    $row['project_name'] ?: 'Workspace',
                    $row['task_title'],
                    $assignee,
                    ucfirst($row['status'] ?? 'Todo'),
                    number_format((float)$row['logged_hours'], 1)
                ];
            }

            if (empty($data)) {
                $data[] = ['N/A', 'No task activity found in date range', 'N/A', 'N/A', '0.0'];
            }

            $userId = (int)auth()->id();

            if ($type === 'csv') {
                $result = \App\Services\ReportService::generateCsv($headers, $data, $userId, ['start' => $start, 'end' => $end]);
            } else {
                // Generate HTML for PDF
                $html = view('manager/reports/templates/pdf_report', [
                    'headers' => $headers,
                    'data'    => $data,
                    'start'   => $start,
                    'end'     => $end
                ]);
                
                $result = \App\Services\ReportService::generatePdf('Team Performance Report', $html, $userId, ['start' => $start, 'end' => $end]);
            }

            if ($this->request->isAJAX() || $this->request->getHeaderLine('accept') === 'application/json') {
                return $this->response->setJSON([
                    'status'       => 'success',
                    'message'      => 'Report generated successfully.',
                    'report'       => $result,
                    'download_url' => site_url('manage/reports/download/' . $result['id']),
                ]);
            }

            return $this->response->download($result['filepath'], null)->setFileName($result['filename']);
        } catch (\Throwable $e) {
            log_message('error', 'Report generation exception: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeaderLine('accept') === 'application/json') {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Report generation failed: ' . $e->getMessage(),
                ]);
            }
            return redirect()->back()->with('error', 'Report generation failed: ' . $e->getMessage());
        }
    }
    
    public function download($id)
    {
        $db = \Config\Database::connect();
        $filepath = null;
        $downloadName = null;

        // 1. Try finding by numeric ID
        if (is_numeric($id) && $db->tableExists('reports')) {
            $report = $db->table('reports')->where('id', (int)$id)->get()->getRowArray();
            if ($report && !empty($report['file_path'])) {
                $candidate = WRITEPATH . 'reports/' . basename($report['file_path']);
                if (file_exists($candidate) && is_file($candidate)) {
                    $filepath = $candidate;
                    $downloadName = basename($report['file_path']);
                }
            }
        }

        // 2. Try finding by filename in DB
        if (!$filepath && $db->tableExists('reports')) {
            $report = $db->table('reports')->where('file_path', (string)$id)->get()->getRowArray();
            if ($report && !empty($report['file_path'])) {
                $candidate = WRITEPATH . 'reports/' . basename($report['file_path']);
                if (file_exists($candidate) && is_file($candidate)) {
                    $filepath = $candidate;
                    $downloadName = basename($report['file_path']);
                }
            }
        }

        // 3. Direct filename on disk
        if (!$filepath) {
            $cleanName = basename((string)$id);
            $candidate = WRITEPATH . 'reports/' . $cleanName;
            if (file_exists($candidate) && is_file($candidate)) {
                $filepath = $candidate;
                $downloadName = $cleanName;
            }
        }

        // 4. Fallback to newest generated report
        if (!$filepath) {
            $files = glob(WRITEPATH . 'reports/report_*.*');
            if (!empty($files)) {
                usort($files, function($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });
                $filepath = $files[0];
                $downloadName = basename($files[0]);
            }
        }

        if ($filepath && file_exists($filepath)) {
            $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            $mime = ($ext === 'pdf') ? 'application/pdf' : 'text/csv';

            return $this->response
                ->setHeader('Content-Type', $mime)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $downloadName . '"')
                ->setHeader('Content-Length', (string)filesize($filepath))
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setBody(file_get_contents($filepath));
        }
        
        return redirect()->back()->with('error', 'Report file not found.');
    }
}

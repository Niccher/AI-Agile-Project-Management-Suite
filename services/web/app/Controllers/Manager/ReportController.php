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
        $type = $this->request->getPost('type') ?? 'pdf';
        $start = $this->request->getPost('period_start');
        $end = $this->request->getPost('period_end');
        
        $db = \Config\Database::connect();
        $builder = $db->table('tasks')
            ->select('projects.name as project_name, tasks.title as task_title, users.username, users.first_name, users.last_name, tasks.status, COALESCE(SUM(time_logs.duration)/3600, 0) as logged_hours')
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
                $assignee = $row['username'] ?: 'Unassigned';
            }
            $data[] = [
                $row['project_name'] ?: 'Workspace',
                $row['task_title'],
                $assignee,
                ucfirst($row['status']),
                number_format((float)$row['logged_hours'], 1)
            ];
        }

        if (empty($data)) {
            $data[] = ['N/A', 'No task activity found in date range', 'N/A', 'N/A', '0.0'];
        }

        if ($type === 'csv') {
            $filepath = \App\Services\ReportService::generateCsv($headers, $data, auth()->id(), ['start' => $start, 'end' => $end]);
            return $this->response->download($filepath, null)->setFileName('report_'.date('Ymd').'.csv');
        } else {
            // Generate HTML for PDF
            $html = view('manager/reports/templates/pdf_report', [
                'headers' => $headers,
                'data' => $data,
                'start' => $start,
                'end' => $end
            ]);
            
            $filepath = \App\Services\ReportService::generatePdf('Team Performance Report', $html, auth()->id(), ['start' => $start, 'end' => $end]);
            return $this->response->download($filepath, null)->setFileName('report_'.date('Ymd').'.pdf');
        }
    }
    
    public function download($id)
    {
        $reportModel = new \App\Models\ReportModel();
        $report = $reportModel->find($id);
        
        if ($report) {
            $filepath = WRITEPATH . 'reports/' . $report['file_path'];
            if (file_exists($filepath)) {
                return $this->response->download($filepath, null);
            }
        }
        
        return redirect()->back()->with('error', 'Report file not found.');
    }
}

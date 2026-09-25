<?php

namespace App\Controllers\User;

use App\Models\ProjectModel;
use App\Models\TimeLogModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class TimeReportController extends BaseUserController
{
    protected TimeLogModel $timeLogModel;
    protected ProjectModel $projectModel;

    public function __construct()
    {
        $this->timeLogModel = new TimeLogModel();
        $this->projectModel = new ProjectModel();
    }

    /**
     * Display Time Tracking & Billing Reports
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->inGroup('admin') || $user->inGroup('manager'));
        $currentUserId = (int) ($this->userId ?? auth()->id() ?? 0);
        
        $filters = [
            'from_date'   => $this->request->getGet('from_date') ?: date('Y-m-01'),
            'to_date'     => $this->request->getGet('to_date') ?: date('Y-m-d'),
            'project_id'  => $this->request->getGet('project_id') ?: '',
            'user_id'     => $this->request->getGet('user_id') ?: ($isAdmin ? '' : $currentUserId),
            'is_billable' => $this->request->getGet('is_billable') ?? 'all',
        ];

        $logs = $this->timeLogModel->getFilteredLogs($currentUserId, $isAdmin, $filters);
        $summary = $this->timeLogModel->getSummaryStats($logs);

        // Projects for filter dropdown
        $projects = $isAdmin 
            ? $this->projectModel->orderBy('name', 'ASC')->findAll()
            : $this->projectModel->where('user_id', $currentUserId)->orderBy('name', 'ASC')->findAll();

        // Team members for filter dropdown (if admin/manager)
        $users = [];
        if ($isAdmin) {
            $userModel = new UserModel();
            $users = $userModel->asArray()->orderBy('username', 'ASC')->findAll();
        }

        return view('user/time_report', [
            'logs'     => $logs,
            'summary'  => $summary,
            'filters'  => $filters,
            'projects' => $projects,
            'users'    => $users,
            'isAdmin'  => $isAdmin,
        ]);
    }

    /**
     * Export report as professional PDF invoice / timesheet
     */
    public function pdf()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->inGroup('admin') || $user->inGroup('manager'));
        $currentUserId = (int) ($this->userId ?? auth()->id() ?? 0);
        
        $filters = [
            'from_date'   => $this->request->getGet('from_date') ?: date('Y-m-01'),
            'to_date'     => $this->request->getGet('to_date') ?: date('Y-m-d'),
            'project_id'  => $this->request->getGet('project_id') ?: '',
            'user_id'     => $this->request->getGet('user_id') ?: ($isAdmin ? '' : $currentUserId),
            'is_billable' => $this->request->getGet('is_billable') ?? 'all',
        ];

        $logs = $this->timeLogModel->getFilteredLogs($currentUserId, $isAdmin, $filters);
        $summary = $this->timeLogModel->getSummaryStats($logs);

        // Selected project name if filtered
        $selectedProject = null;
        if (! empty($filters['project_id'])) {
            $selectedProject = $this->projectModel->find($filters['project_id']);
        }

        $html = view('reports/time_report_pdf', [
            'logs'            => $logs,
            'summary'         => $summary,
            'filters'         => $filters,
            'selectedProject' => $selectedProject,
            'currentUser'     => auth()->user(),
            'siteName'        => setting('App.siteName') ?? 'Chege Jira',
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Timesheet_Report_' . date('Ymd_His') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    /**
     * Export report as CSV
     */
    public function csv()
    {
        $user = auth()->user();
        $isAdmin = $user && ($user->inGroup('admin') || $user->inGroup('manager'));
        $currentUserId = (int) ($this->userId ?? auth()->id() ?? 0);
        
        $filters = [
            'from_date'   => $this->request->getGet('from_date') ?: date('Y-m-01'),
            'to_date'     => $this->request->getGet('to_date') ?: date('Y-m-d'),
            'project_id'  => $this->request->getGet('project_id') ?: '',
            'user_id'     => $this->request->getGet('user_id') ?: ($isAdmin ? '' : $currentUserId),
            'is_billable' => $this->request->getGet('is_billable') ?? 'all',
        ];

        $logs = $this->timeLogModel->getFilteredLogs($currentUserId, $isAdmin, $filters);

        $filename = 'Timesheet_Export_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'User', 'Project', 'Task / Activity', 'Start Time', 'End Time', 'Duration (Hours)', 'Billable', 'Hourly Rate ($)', 'Total ($)', 'Notes']);

        foreach ($logs as $log) {
            $hours = round(($log['duration'] ?? 0) / 3600, 2);
            $isBillable = (int)($log['is_billable'] ?? 1) === 1 ? 'Yes' : 'No';
            $rate = (float)($log['hourly_rate'] ?? 50.00);
            $total = (int)($log['is_billable'] ?? 1) === 1 ? round($hours * $rate, 2) : 0.00;

            fputcsv($output, [
                $log['id'],
                $log['user_name'] ?? 'User #' . $log['user_id'],
                $log['project_name'] ?? 'General',
                $log['task_name'],
                $log['start_time'],
                $log['end_time'] ?? '',
                $hours,
                $isBillable,
                $rate,
                $total,
                $log['notes'] ?? '',
            ]);
        }

        rewind($output);
        $csvData = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvData);
    }
}

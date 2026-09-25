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
        
        // Dummy data for now, ideally fetch from models based on period
        $headers = ['Project', 'Task', 'Assignee', 'Status', 'Logged Time (hrs)'];
        $data = [
            ['Website Redesign', 'Update homepage layout', 'John Doe', 'approved', '4.5'],
            ['API Integration', 'Stripe checkout webhook', 'Jane Smith', 'in_progress', '12.0'],
            ['Mobile App', 'Fix login bug', 'John Doe', 'done', '2.0'],
        ];

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

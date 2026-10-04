<?php

namespace App\Controllers\Manager;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $reports = [];
        $pdfCount = 0;
        $csvCount = 0;

        try {
            // 1. Ensure reports table and all required columns exist
            if (!$db->tableExists('reports')) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                    'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                    'name' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
                    'type' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'pdf'],
                    'parameters' => ['type' => 'TEXT', 'null' => true],
                    'file_path' => ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true],
                    'status' => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'completed'],
                    'created_at' => ['type' => 'DATETIME', 'null' => true],
                    'updated_at' => ['type' => 'DATETIME', 'null' => true],
                ]);
                $forge->addKey('id', true);
                $forge->createTable('reports', true);
            } else {
                $cols = $db->getFieldNames('reports') ?? [];
                $addCols = [];
                if (!in_array('user_id', $cols, true) && !in_array('created_by', $cols, true)) {
                    $addCols['user_id'] = ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true];
                }
                if (!in_array('name', $cols, true)) {
                    $addCols['name'] = ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true];
                }
                if (!in_array('type', $cols, true)) {
                    $addCols['type'] = ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'pdf'];
                }
                if (!in_array('parameters', $cols, true)) {
                    $addCols['parameters'] = ['type' => 'TEXT', 'null' => true];
                }
                if (!in_array('file_path', $cols, true)) {
                    $addCols['file_path'] = ['type' => 'VARCHAR', 'constraint' => '255', 'null' => true];
                }
                if (!in_array('status', $cols, true)) {
                    $addCols['status'] = ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'completed'];
                }
                if (!in_array('created_at', $cols, true)) {
                    $addCols['created_at'] = ['type' => 'DATETIME', 'null' => true];
                }
                if (!in_array('updated_at', $cols, true)) {
                    $addCols['updated_at'] = ['type' => 'DATETIME', 'null' => true];
                }
                if (!empty($addCols)) {
                    $forge = \Config\Database::forge();
                    $forge->addColumn('reports', $addCols);
                }
            }

            // 2. Synchronize any generated report files on disk into DB
            $diskFiles = glob(WRITEPATH . 'reports/report_*.*') ?: [];
            if (!empty($diskFiles)) {
                $cols = $db->getFieldNames('reports') ?? [];
                $loggedPaths = [];
                if (in_array('file_path', $cols, true)) {
                    $existingRows = $db->table('reports')->select('file_path')->get()->getResultArray();
                    $loggedPaths = array_column($existingRows, 'file_path');
                }

                $defaultUserId = (int)(auth()->id() ?? 1);
                if ($db->tableExists('users')) {
                    $userExists = $db->table('users')->where('id', $defaultUserId)->countAllResults();
                    if ($userExists == 0) {
                        $firstU = $db->table('users')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
                        $defaultUserId = $firstU ? (int)$firstU['id'] : null;
                    }
                }

                foreach ($diskFiles as $df) {
                    $base = basename($df);
                    if (!in_array($base, $loggedPaths, true)) {
                        $ext = strtolower(pathinfo($df, PATHINFO_EXTENSION));
                        $mtime = filemtime($df);
                        $ins = [
                            'user_id'    => $defaultUserId,
                            'name'       => 'Team Performance Report',
                            'type'       => $ext ?: 'pdf',
                            'parameters' => json_encode(['start' => null, 'end' => null]),
                            'file_path'  => $base,
                            'status'     => 'completed',
                            'created_at' => date('Y-m-d H:i:s', $mtime),
                            'updated_at' => date('Y-m-d H:i:s', $mtime),
                        ];
                        $validIns = array_intersect_key($ins, array_flip($cols));
                        if (!empty($validIns)) {
                            $db->table('reports')->insert($validIns);
                        }
                    }
                }
            }

            // 3. Query reports from DB
            $builder = $db->table('reports');
            $cols = $db->getFieldNames('reports') ?? [];
            if (in_array('created_at', $cols, true)) {
                $builder->orderBy('created_at', 'DESC');
            } else {
                $builder->orderBy('id', 'DESC');
            }
            $reports = $builder->limit(50)->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'Report index error: ' . $e->getMessage());
        }

        // 4. Fallback to disk scan if DB reports are empty
        if (empty($reports)) {
            $diskFiles = glob(WRITEPATH . 'reports/report_*.*') ?: [];
            foreach ($diskFiles as $df) {
                $ext = strtolower(pathinfo($df, PATHINFO_EXTENSION));
                $mtime = filemtime($df);
                $reports[] = [
                    'id'         => basename($df),
                    'name'       => 'Team Performance Report',
                    'type'       => $ext ?: 'pdf',
                    'parameters' => json_encode(['start' => null, 'end' => null]),
                    'file_path'  => basename($df),
                    'status'     => 'completed',
                    'created_at' => date('Y-m-d H:i:s', $mtime),
                    'updated_at' => date('Y-m-d H:i:s', $mtime),
                ];
            }
        }

        foreach ($reports as $r) {
            $t = strtolower($r['type'] ?? 'pdf');
            if ($t === 'pdf') $pdfCount++;
            if ($t === 'csv') $csvCount++;
        }

        return view('manager/reports/index', [
            'reports'      => $reports,
            'totalReports' => count($reports),
            'pdfCount'     => $pdfCount,
            'csvCount'     => $csvCount,
        ]);
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
            return $this->response->download($filepath, null)->setFileName($downloadName);
        }
        
        return redirect()->back()->with('error', 'Report file not found.');
    }

    public function delete($id)
    {
        try {
            $db = \Config\Database::connect();
            $deleted = false;

            // 1. Try finding by numeric ID
            if (is_numeric($id) && $db->tableExists('reports')) {
                $report = $db->table('reports')->where('id', (int)$id)->get()->getRowArray();
                if ($report) {
                    if (!empty($report['file_path'])) {
                        $filepath = WRITEPATH . 'reports/' . basename($report['file_path']);
                        if (file_exists($filepath) && is_file($filepath)) {
                            @unlink($filepath);
                        }
                    }
                    $db->table('reports')->where('id', (int)$id)->delete();
                    $deleted = true;
                }
            }

            // 2. Try finding by filename in DB
            if (!$deleted && $db->tableExists('reports')) {
                $report = $db->table('reports')->where('file_path', (string)$id)->get()->getRowArray();
                if ($report) {
                    $filepath = WRITEPATH . 'reports/' . basename($report['file_path']);
                    if (file_exists($filepath) && is_file($filepath)) {
                        @unlink($filepath);
                    }
                    $db->table('reports')->where('id', $report['id'])->delete();
                    $deleted = true;
                }
            }

            // 3. Direct file removal if exists on disk
            $cleanName = basename((string)$id);
            $diskCandidate = WRITEPATH . 'reports/' . $cleanName;
            if (file_exists($diskCandidate) && is_file($diskCandidate)) {
                @unlink($diskCandidate);
                $deleted = true;
            }

            if ($this->request->isAJAX() || $this->request->getHeaderLine('accept') === 'application/json') {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Report deleted successfully.',
                    'id'      => $id
                ]);
            }

            return redirect()->back()->with('message', 'Report deleted successfully.');
        } catch (\Throwable $e) {
            log_message('error', 'Report deletion exception: ' . $e->getMessage());
            if ($this->request->isAJAX() || $this->request->getHeaderLine('accept') === 'application/json') {
                return $this->response->setStatusCode(500)->setJSON([
                    'status'  => 'error',
                    'message' => 'Failed to delete report: ' . $e->getMessage()
                ]);
            }
            return redirect()->back()->with('error', 'Failed to delete report: ' . $e->getMessage());
        }
    }
}


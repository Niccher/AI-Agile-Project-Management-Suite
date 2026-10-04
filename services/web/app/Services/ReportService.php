<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use League\Csv\Writer;
use App\Models\ReportModel;

class ReportService
{
    protected static function ensureReportsTableExists($db)
    {
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
    }

    protected static function resolveValidUserId($db, int $generatedBy): ?int
    {
        if ($generatedBy > 0 && $db->tableExists('users')) {
            $userExists = $db->table('users')->where('id', $generatedBy)->countAllResults();
            if ($userExists > 0) {
                return $generatedBy;
            }
        }

        $sessionUserId = (int)(auth()->id() ?? session('user_id') ?? 0);
        if ($sessionUserId > 0 && $db->tableExists('users')) {
            $userExists = $db->table('users')->where('id', $sessionUserId)->countAllResults();
            if ($userExists > 0) {
                return $sessionUserId;
            }
        }

        if ($db->tableExists('users')) {
            $firstUser = $db->table('users')->select('id')->orderBy('id', 'ASC')->get()->getRowArray();
            if ($firstUser) {
                return (int)$firstUser['id'];
            }
        }

        return null;
    }

    public static function generatePdf(string $title, string $htmlContent, int $generatedBy, array $params = [])
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($htmlContent);
        // Default to landscape for tables
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        // Create directory if not exists
        $dir = WRITEPATH . 'reports/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = 'report_' . time() . '_' . uniqid() . '.pdf';
        $filepath = $dir . $filename;
        
        file_put_contents($filepath, $dompdf->output());

        $reportId = 0;
        try {
            $db = \Config\Database::connect();
            self::ensureReportsTableExists($db);
            
            $validUserId = self::resolveValidUserId($db, $generatedBy);
            $reportFields = $db->getFieldNames('reports') ?? [];
            $insertData = [
                'name'       => $title,
                'type'       => 'pdf',
                'parameters' => json_encode($params),
                'file_path'  => $filename,
                'status'     => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (in_array('user_id', $reportFields, true)) {
                $insertData['user_id'] = $validUserId;
            } elseif (in_array('created_by', $reportFields, true)) {
                $insertData['created_by'] = $validUserId;
            }

            $validData = array_intersect_key($insertData, array_flip($reportFields));
            if (!empty($validData)) {
                $db->table('reports')->insert($validData);
                $reportId = (int)$db->insertID();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Report logging error: ' . $e->getMessage());
        }

        return [
            'id'         => $reportId ?: $filename,
            'filename'   => $filename,
            'filepath'   => $filepath,
            'name'       => $title,
            'type'       => 'pdf',
            'parameters' => $params,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public static function generateCsv(array $headers, array $data, int $generatedBy, array $params = [])
    {
        $dir = WRITEPATH . 'reports/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $filename = 'report_' . time() . '_' . uniqid() . '.csv';
        $filepath = $dir . $filename;

        $csv = Writer::createFromPath($filepath, 'w+');
        $csv->insertOne($headers);
        $csv->insertAll($data);

        // Log to database
        $reportId = 0;
        try {
            $db = \Config\Database::connect();
            self::ensureReportsTableExists($db);

            $validUserId = self::resolveValidUserId($db, $generatedBy);
            $reportFields = $db->getFieldNames('reports') ?? [];
            $insertData = [
                'name'       => 'Team Performance Report',
                'type'       => 'csv',
                'parameters' => json_encode($params),
                'file_path'  => $filename,
                'status'     => 'completed',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if (in_array('user_id', $reportFields, true)) {
                $insertData['user_id'] = $validUserId;
            } elseif (in_array('created_by', $reportFields, true)) {
                $insertData['created_by'] = $validUserId;
            }

            $validData = array_intersect_key($insertData, array_flip($reportFields));
            if (!empty($validData)) {
                $db->table('reports')->insert($validData);
                $reportId = (int)$db->insertID();
            }
        } catch (\Throwable $e) {
            log_message('error', 'Report logging error: ' . $e->getMessage());
        }

        return [
            'id'         => $reportId ?: $filename,
            'filename'   => $filename,
            'filepath'   => $filepath,
            'name'       => 'Team Performance Report',
            'type'       => 'csv',
            'parameters' => $params,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}

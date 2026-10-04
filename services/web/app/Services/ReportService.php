<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use League\Csv\Writer;
use App\Models\ReportModel;

class ReportService
{
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

        $periodStart = !empty($params['start']) ? $params['start'] : null;
        $periodEnd = !empty($params['end']) ? $params['end'] : null;

        $reportId = 0;
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('reports')) {
                $reportFields = $db->getFieldNames('reports') ?? [];
                $insertData = [
                    'name'       => $title,
                    'type'       => 'pdf',
                    'parameters' => json_encode($params),
                    'file_path'  => $filename,
                    'status'     => 'completed',
                ];
                if (in_array('user_id', $reportFields, true)) {
                    $insertData['user_id'] = $generatedBy;
                } elseif (in_array('created_by', $reportFields, true)) {
                    $insertData['created_by'] = $generatedBy;
                }

                $validData = array_intersect_key($insertData, array_flip($reportFields));
                $reportModel = new ReportModel();
                $reportId = $reportModel->insert($validData);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Report logging error: ' . $e->getMessage());
        }

        return [
            'id'         => $reportId ?: time(),
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
            if ($db->tableExists('reports')) {
                $reportFields = $db->getFieldNames('reports') ?? [];
                $insertData = [
                    'name'       => 'Team Performance Report',
                    'type'       => 'csv',
                    'parameters' => json_encode($params),
                    'file_path'  => $filename,
                    'status'     => 'completed',
                ];
                if (in_array('user_id', $reportFields, true)) {
                    $insertData['user_id'] = $generatedBy;
                } elseif (in_array('created_by', $reportFields, true)) {
                    $insertData['created_by'] = $generatedBy;
                }

                $validData = array_intersect_key($insertData, array_flip($reportFields));
                $reportModel = new ReportModel();
                $reportId = $reportModel->insert($validData);
            }
        } catch (\Throwable $e) {
            log_message('error', 'Report logging error: ' . $e->getMessage());
        }

        return [
            'id'         => $reportId ?: time(),
            'filename'   => $filename,
            'filepath'   => $filepath,
            'name'       => 'Team Performance Report',
            'type'       => 'csv',
            'parameters' => $params,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }
}

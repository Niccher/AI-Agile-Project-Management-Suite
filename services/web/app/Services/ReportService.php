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

        // Log to database
        $reportModel = new ReportModel();
        $reportModel->insert([
            'generated_by' => $generatedBy,
            'type'         => 'pdf',
            'period_start' => $params['start'] ?? null,
            'period_end'   => $params['end'] ?? null,
            'file_path'    => $filename,
            'params'       => json_encode($params),
        ]);

        return $filepath;
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
        $reportModel = new ReportModel();
        $reportModel->insert([
            'generated_by' => $generatedBy,
            'type'         => 'csv',
            'period_start' => $params['start'] ?? null,
            'period_end'   => $params['end'] ?? null,
            'file_path'    => $filename,
            'params'       => json_encode($params),
        ]);

        return $filepath;
    }
}

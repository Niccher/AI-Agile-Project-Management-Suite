<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DatabaseBackup extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Create a compressed SQL backup of the active database.';

    public function run(array $params)
    {
        CLI::write('Starting database backup...', 'yellow');

        $result = self::createBackup();

        if ($result['status'] === 'success') {
            CLI::write("Backup created successfully: {$result['filename']} ({$result['filesize']})", 'green');
            CLI::write("File saved at: {$result['filepath']}", 'green');
        } else {
            CLI::error("Backup failed: " . $result['message']);
        }
    }

    /**
     * Reusable programmatic backup creator
     */
    public static function createBackup(): array
    {
        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0755, true);
        }

        $db = \Config\Database::connect();
        $dbName = $db->database ?? 'database';
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_{$dbName}_{$timestamp}.sql";
        $filepath = $backupDir . $filename;
        $gzFilename = $filename . '.gz';
        $gzFilepath = $backupDir . $gzFilename;

        try {
            $handle = fopen($filepath, 'w+');
            if (!$handle) {
                return ['status' => 'error', 'message' => 'Unable to create backup file in writable/backups/'];
            }

            // Write SQL Dump Header
            fwrite($handle, "-- ========================================================\n");
            fwrite($handle, "-- Database Backup: {$dbName}\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s T') . "\n");
            fwrite($handle, "-- System: " . (setting('App.siteName') ?? 'Chege Jira') . " v1.0.0\n");
            fwrite($handle, "-- ========================================================\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($handle, "SET AUTOCOMMIT = 0;\n");
            fwrite($handle, "START TRANSACTION;\n\n");

            $tables = $db->listTables();

            foreach ($tables as $table) {
                // Table structure
                fwrite($handle, "\n-- --------------------------------------------------------\n");
                fwrite($handle, "-- Structure for table `{$table}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTableQuery = $db->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createTableQuery->getRowArray();
                $createSql = $createRow['Create Table'] ?? $createRow['Create View'] ?? null;

                if ($createSql) {
                    fwrite($handle, $createSql . ";\n\n");
                }

                // Table data
                $rowsQuery = $db->table($table)->get();
                $rows = $rowsQuery->getResultArray();

                if (!empty($rows)) {
                    fwrite($handle, "-- Dumping data for table `{$table}`\n");
                    
                    $columns = array_keys($rows[0]);
                    $colList = '`' . implode('`, `', $columns) . '`';

                    foreach (array_chunk($rows, 100) as $chunk) {
                        $valuesList = [];
                        foreach ($chunk as $row) {
                            $escaped = array_map(function ($val) use ($db) {
                                if ($val === null) return 'NULL';
                                return $db->escape($val);
                            }, array_values($row));
                            $valuesList[] = '(' . implode(', ', $escaped) . ')';
                        }
                        fwrite($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $valuesList) . ";\n");
                    }
                    fwrite($handle, "\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fwrite($handle, "COMMIT;\n");
            fclose($handle);

            // Compress with gzip if zlib available
            if (function_exists('gzopen')) {
                $fpOut = gzopen($gzFilepath, 'wb9');
                $fpIn = fopen($filepath, 'rb');
                while (!feof($fpIn)) {
                    gzwrite($fpOut, fread($fpIn, 1024 * 512));
                }
                fclose($fpIn);
                gzclose($fpOut);
                @unlink($filepath);

                $finalPath = $gzFilepath;
                $finalName = $gzFilename;
            } else {
                $finalPath = $filepath;
                $finalName = $filename;
            }

            $bytes = filesize($finalPath);
            $filesize = ($bytes >= 1048576) ? round($bytes / 1048576, 2) . ' MB' : round($bytes / 1024, 2) . ' KB';

            // Prune backups older than 30 days
            self::pruneOldBackups($backupDir, 30);

            return [
                'status'   => 'success',
                'filename' => $finalName,
                'filepath' => $finalPath,
                'filesize' => $filesize,
                'created'  => date('Y-m-d H:i:s'),
            ];
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Prune backups older than $days
     */
    protected static function pruneOldBackups(string $dir, int $days = 30)
    {
        $cutoff = time() - ($days * 86400);
        foreach (glob($dir . 'backup_*.*') as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /**
     * Get list of existing backups
     */
    public static function listBackups(): array
    {
        $backupDir = WRITEPATH . 'backups/';
        if (!is_dir($backupDir)) {
            return [];
        }

        $files = glob($backupDir . 'backup_*.*');
        $backups = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $bytes = filesize($file);
                $size = ($bytes >= 1048576) ? round($bytes / 1048576, 2) . ' MB' : round($bytes / 1024, 2) . ' KB';
                $backups[] = [
                    'filename'  => basename($file),
                    'size'      => $size,
                    'created'   => date('Y-m-d H:i:s', filemtime($file)),
                    'timestamp' => filemtime($file),
                ];
            }
        }

        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        return $backups;
    }
}
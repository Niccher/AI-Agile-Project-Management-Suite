<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * SessionGc
 *
 * Garbage collection command for the MySQL ci_sessions table.
 * When the platform operates in degraded fallback mode, sessions are stored in MySQL.
 * This command prunes expired sessions to maintain optimal database query speeds.
 */
class SessionGc extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'session:gc';
    protected $description = 'Purges expired session records from the MySQL ci_sessions fallback table.';
    protected $usage       = 'session:gc [options]';
    protected $arguments   = [];
    protected $options     = [
        '--dry-run' => 'Simulate session cleanup without deleting any database records.',
        '--max-age' => 'Custom session lifetime in seconds (defaults to Config\\Session::$expiration).',
    ];

    public function run(array $params)
    {
        CLI::write("==================================================", 'yellow');
        CLI::write("  Session Garbage Collection (ci_sessions)        ", 'yellow');
        CLI::write("==================================================", 'yellow');

        $isDryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $sessionConfig = config('Session');
        $expiration = (int)($params['max-age'] ?? CLI::getOption('max-age') ?? $sessionConfig->expiration ?? 2592000);
        $cutoffTimestamp = time() - $expiration;

        CLI::write(sprintf("Session TTL Policy : %d seconds (~%d days)", $expiration, round($expiration / 86400)));
        CLI::write(sprintf("Cutoff Timestamp   : %d (%s UTC)", $cutoffTimestamp, gmdate('Y-m-d H:i:s', $cutoffTimestamp)));
        CLI::write(sprintf("Execution Mode     : %s", $isDryRun ? 'DRY-RUN (No records will be removed)' : 'LIVE EXECUTION'));

        try {
            $db = \Config\Database::connect();
            $tableName = 'ci_sessions';

            // Verify table exists
            if (!$db->tableExists($tableName)) {
                CLI::error(sprintf("Table '%s' does not exist in database '%s'. Nothing to prune.", $tableName, $db->database));
                return;
            }

            // Count total and expired sessions
            $totalCount = $db->table($tableName)->countAllResults();
            $expiredQuery = $db->table($tableName)->where('timestamp <', $cutoffTimestamp);
            $expiredCount = $expiredQuery->countAllResults(false);

            CLI::write(sprintf("Total Sessions     : %d", $totalCount));
            CLI::write(sprintf("Expired Sessions   : %d", $expiredCount), $expiredCount > 0 ? 'light_red' : 'green');

            if ($expiredCount === 0) {
                CLI::write("No expired sessions found. Database is clean.", 'green');
                return;
            }

            if ($isDryRun) {
                CLI::write(sprintf("[DRY-RUN] Would have deleted %d expired sessions.", $expiredCount), 'cyan');
                return;
            }

            $startTime = microtime(true);
            $db->table($tableName)->where('timestamp <', $cutoffTimestamp)->delete();
            $deletedRows = $db->affectedRows();
            $elapsedMs = round((microtime(true) - $startTime) * 1000.0, 2);

            CLI::write(sprintf("Successfully purged %d expired sessions in %.2fms.", $deletedRows, $elapsedMs), 'green');
        } catch (\Throwable $e) {
            CLI::error("Session garbage collection failed: " . $e->getMessage());
        }
    }
}

<?php

namespace App\Commands;

use App\Models\TaskAttachmentModel;
use App\Services\StorageService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class StorageMigrate extends BaseCommand
{
    protected $group       = 'Storage';
    protected $name        = 'storage:migrate-to-s3';
    protected $description = 'Migrate existing local uploads and task attachments into MinIO S3 object storage.';
    protected $options     = [
        '--dry-run' => 'Simulate migration without modifying S3 buckets or database records.',
    ];

    public function run(array $params)
    {
        $dryRun = array_key_exists('dry-run', $params) || CLI::getOption('dry-run');
        $storage = new StorageService();

        CLI::write("=== Storage Migration to MinIO S3 ===", 'cyan');

        if (!$storage->isEnabled()) {
            CLI::error("S3 storage is disabled or S3_ENDPOINT is not configured in .env.");
            return;
        }

        if ($dryRun) {
            CLI::write("[DRY RUN MODE] No files will be uploaded or deleted.", 'yellow');
        }

        $attachmentsBucket = (string) env('S3_BUCKET_ATTACHMENTS', 'agile-attachments');
        $avatarsBucket     = (string) env('S3_BUCKET_AVATARS', 'agile-avatars');
        $backupsBucket     = (string) env('S3_BUCKET_BACKUPS', 'agile-backups');

        // Always verify and provision S3 storage buckets
        $storage->ensureBucket($attachmentsBucket);
        $storage->ensureBucket($avatarsBucket);
        $storage->ensureBucket($backupsBucket);

        // 1. Migrate Task Attachments
        CLI::write("\nScanning Task Attachments...", 'yellow');
        $attachmentModel = new TaskAttachmentModel();
        $attachments = $attachmentModel->findAll();
        $migratedAttachments = 0;
        $skippedAttachments = 0;
        $missingLocal = 0;

        foreach ($attachments as $att) {
            $taskId   = $att['task_id'];
            $fileName = $att['file_name'];
            $s3Key    = "tasks/{$taskId}/{$fileName}";
            $localPath = WRITEPATH . 'uploads/tasks/' . $fileName;

            if (!file_exists($localPath)) {
                $missingLocal++;
                CLI::write("  [MISSING] Task #{$taskId}: {$fileName} not found on local disk.", 'red');
                continue;
            }

            if (!$dryRun && $storage->hasObject($attachmentsBucket, $s3Key)) {
                $skippedAttachments++;
                CLI::write("  [EXISTS] Task #{$taskId}: {$fileName} already in S3.", 'white');
                continue;
            }

            if ($dryRun) {
                CLI::write("  [WOULD MIGRATE] Task #{$taskId}: {$fileName} -> {$attachmentsBucket}/{$s3Key}", 'cyan');
                $migratedAttachments++;
            } else {
                $success = $storage->uploadFile($attachmentsBucket, $s3Key, $localPath, $att['mime_type']);
                if ($success) {
                    $migratedAttachments++;
                    CLI::write("  [MIGRATED] Task #{$taskId}: {$fileName} -> S3", 'green');
                } else {
                    CLI::error("  [FAILED] Task #{$taskId}: Failed uploading {$fileName}");
                }
            }
        }

        // 2. Migrate User Avatars
        CLI::write("\nScanning User Avatars...", 'yellow');
        $avatarDir = WRITEPATH . 'uploads/avatars/';
        $migratedAvatars = 0;
        $skippedAvatars  = 0;

        if (is_dir($avatarDir)) {
            $avatarFiles = glob($avatarDir . '*');
            foreach ($avatarFiles as $avatarFile) {
                if (!is_file($avatarFile)) {
                    continue;
                }
                $baseName = basename($avatarFile);
                if ($baseName === 'index.html' || $baseName === '.gitkeep') {
                    continue;
                }

                $s3Key = "avatars/{$baseName}";
                if (!$dryRun && $storage->hasObject($avatarsBucket, $s3Key)) {
                    $skippedAvatars++;
                    CLI::write("  [EXISTS] Avatar: {$baseName} already in S3.", 'white');
                    continue;
                }

                $mimeType = mime_content_type($avatarFile) ?: 'image/jpeg';
                if ($dryRun) {
                    CLI::write("  [WOULD MIGRATE] Avatar: {$baseName} -> {$avatarsBucket}/{$s3Key}", 'cyan');
                    $migratedAvatars++;
                } else {
                    $success = $storage->uploadFile($avatarsBucket, $s3Key, $avatarFile, $mimeType);
                    if ($success) {
                        $migratedAvatars++;
                        CLI::write("  [MIGRATED] Avatar: {$baseName} -> S3", 'green');
                    } else {
                        CLI::error("  [FAILED] Avatar: Failed uploading {$baseName}");
                    }
                }
            }
        }

        // 3. Migrate Database Backups
        CLI::write("\nScanning Database Backups...", 'yellow');
        $backupDir = WRITEPATH . 'backups/';
        $migratedBackups = 0;
        $skippedBackups  = 0;

        if (is_dir($backupDir)) {
            $backupFiles = glob($backupDir . '*.sql*');
            if ($backupFiles) {
                foreach ($backupFiles as $backupFile) {
                    if (!is_file($backupFile)) {
                        continue;
                    }
                    $baseName = basename($backupFile);
                    $s3Key = "mysql/{$baseName}";

                    if (!$dryRun && $storage->hasObject($backupsBucket, $s3Key)) {
                        $skippedBackups++;
                        CLI::write("  [EXISTS] Backup: {$baseName} already in S3.", 'white');
                        continue;
                    }

                    if ($dryRun) {
                        CLI::write("  [WOULD MIGRATE] Backup: {$baseName} -> {$backupsBucket}/{$s3Key}", 'cyan');
                        $migratedBackups++;
                    } else {
                        $success = $storage->uploadFile($backupsBucket, $s3Key, $backupFile, 'application/gzip');
                        if ($success) {
                            $migratedBackups++;
                            CLI::write("  [MIGRATED] Backup: {$baseName} -> S3", 'green');
                        } else {
                            CLI::error("  [FAILED] Backup: Failed uploading {$baseName}");
                        }
                    }
                }
            }
        }

        // Summary
        CLI::write("\n=== Migration Summary ===", 'cyan');
        CLI::write("Task Attachments Migrated: {$migratedAttachments}");
        CLI::write("Task Attachments Skipped:  {$skippedAttachments}");
        CLI::write("Task Attachments Missing:  {$missingLocal}");
        CLI::write("User Avatars Migrated:     {$migratedAvatars}");
        CLI::write("User Avatars Skipped:      {$skippedAvatars}");
        CLI::write("Database Backups Migrated: {$migratedBackups}");
        CLI::write("Database Backups Skipped:  {$skippedBackups}");
        CLI::write("Completed successfully.\n", 'green');
    }
}

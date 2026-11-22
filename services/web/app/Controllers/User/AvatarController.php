<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Services\StorageService;
use CodeIgniter\Exceptions\PageNotFoundException;

class AvatarController extends BaseUserController
{
    protected StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService();
    }

    /**
     * Serve user avatar safely with path traversal protection and S3 offload.
     *
     * @param string $filename
     * @return mixed
     * @throws PageNotFoundException
     */
    public function show(string $filename)
    {
        // 1. Path traversal defense: strip directories and enforce strict alphanumeric naming
        $cleanName = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $cleanName)) {
            throw PageNotFoundException::forPageNotFound('Invalid avatar filename.');
        }

        $bucket = (string) env('S3_BUCKET_AVATARS', 'agile-avatars');
        $s3Key = "avatars/{$cleanName}";

        // 2. MinIO S3 Object Storage (if configured)
        if ($this->storage->isEnabled()) {
            if ($this->storage->useDirectPresigned()) {
                $presignedUrl = $this->storage->getPresignedUrl($bucket, $s3Key, 1800);
                return $this->response->redirect($presignedUrl, 'auto', 302);
            }

            $s3Object = $this->storage->getObject($bucket, $s3Key);
            if ($s3Object !== null) {
                return $this->response
                    ->setContentType($s3Object['mime_type'])
                    ->setBody($s3Object['body']);
            }
        }

        // 3. Fallback to Local Disk with canonical directory confinement check
        $avatarDir = realpath(WRITEPATH . 'uploads/avatars');
        if ($avatarDir === false) {
            throw PageNotFoundException::forPageNotFound('Avatar storage directory not found.');
        }

        $path = $avatarDir . DIRECTORY_SEPARATOR . $cleanName;
        $realPath = realpath($path);

        // Security gate: ensure resolved realpath is strictly inside the avatar directory
        if ($realPath === false || !str_starts_with($realPath, $avatarDir . DIRECTORY_SEPARATOR)) {
            throw PageNotFoundException::forPageNotFound('Avatar file does not exist.');
        }

        $mime = mime_content_type($realPath) ?: 'image/png';

        return $this->response
            ->setContentType($mime)
            ->setBody((string) file_get_contents($realPath));
    }
}

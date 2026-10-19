<?php

namespace App\Controllers\User;

use App\Services\StorageService;

class AvatarController extends BaseUserController
{
    protected StorageService $storage;

    public function __construct()
    {
        $this->storage = new StorageService();
    }

    public function show($filename)
    {
        $bucket = (string) env('S3_BUCKET_AVATARS', 'agile-avatars');
        $s3Key = "avatars/{$filename}";

        // 1. MinIO S3 Object Storage
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

        // 2. Fallback to Local Disk
        $path = WRITEPATH . 'uploads/avatars/' . $filename;
        if (!file_exists($path)) {
            // Provide a default avatar or 404
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return $this->response
                    ->setContentType($mime)
                    ->setBody(file_get_contents($path));
    }
}

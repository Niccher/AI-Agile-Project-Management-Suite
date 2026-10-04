<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TaskAttachmentModel;
use App\Models\TaskModel;
use App\Services\ActivityLogger;

class TaskAttachmentApiController extends BaseController
{
    private const MAX_SIZE_BYTES = 20971520; // 20 MB

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
    ];

    public function list(int $taskId)
    {
        $attachmentModel = new TaskAttachmentModel();
        $attachments = $attachmentModel->getAttachmentsForTask($taskId);

        return $this->response->setJSON([
            'status'      => 'success',
            'attachments' => $attachments,
        ]);
    }

    public function upload(int $taskId)
    {
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if (!$task) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Task not found',
            ]);
        }

        $file = $this->request->getFile('file') ?? $this->request->getFile('attachment');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $file ? $file->getErrorString() : 'No file uploaded or invalid upload.',
            ]);
        }

        // 1. Enforce Max 20MB size
        $fileSize = $file->getSize();
        if ($fileSize > self::MAX_SIZE_BYTES) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File size exceeds maximum permitted limit of 20MB.',
            ]);
        }

        // 2. Perform Deep Magic Byte / MIME sniffing
        $tempPath = $file->getTempName();
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($tempPath);

        // SVG fallback detection
        if ($detectedMime === 'text/plain' || $detectedMime === 'text/xml' || $detectedMime === 'image/svg+xml') {
            $contentHead = file_get_contents($tempPath, false, null, 0, 512);
            if (str_contains($contentHead, '<svg')) {
                $detectedMime = 'image/svg+xml';
            }
        }

        if (!in_array($detectedMime, self::ALLOWED_MIME_TYPES, true)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Security Error: Only Image files (JPEG, PNG, GIF, WEBP, SVG) and PDF documents are allowed.',
            ]);
        }

        // 3. Move file to writable/uploads/tasks/
        $uploadDir = WRITEPATH . 'uploads/tasks/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $originalName = $file->getClientName();
        $safeFileName = $file->getRandomName();

        if (!$file->move($uploadDir, $safeFileName)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to save uploaded file.',
            ]);
        }

        $userId = auth()->id();
        $attachmentModel = new TaskAttachmentModel();
        $attachmentId = $attachmentModel->insert([
            'task_id'       => $taskId,
            'user_id'       => $userId,
            'file_name'     => $safeFileName,
            'original_name' => $originalName,
            'mime_type'     => $detectedMime,
            'file_size'     => $fileSize,
            'file_path'     => 'writable/uploads/tasks/' . $safeFileName,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $attachment = $attachmentModel->find($attachmentId);
        $user = auth()->user();
        $attachment['username'] = $user->username ?? 'User';

        // Log Activity
        ActivityLogger::log($taskId, 'attachment_added', "Attached file: {$originalName}", $userId);

        return $this->response->setJSON([
            'status'     => 'success',
            'message'    => 'File attached successfully.',
            'attachment' => $attachment,
        ]);
    }

    public function download(int $id)
    {
        $attachmentModel = new TaskAttachmentModel();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            return $this->response->setStatusCode(404)->setBody('Attachment not found');
        }

        $filePath = WRITEPATH . 'uploads/tasks/' . $attachment['file_name'];
        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File does not exist on disk');
        }

        return $this->response
            ->setHeader('Content-Type', $attachment['mime_type'])
            ->setHeader('Content-Disposition', 'inline; filename="' . addslashes($attachment['original_name']) . '"')
            ->setBody(file_get_contents($filePath));
    }

    public function delete(int $id)
    {
        $attachmentModel = new TaskAttachmentModel();
        $attachment = $attachmentModel->find($id);

        if (!$attachment) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Attachment not found']);
        }

        $userId = auth()->id();
        $currentUser = auth()->user();
        $isAdmin = $currentUser && ($currentUser->inGroup('admin') || $currentUser->inGroup('manager'));

        if (!$isAdmin && (int)$attachment['user_id'] !== (int)$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Permission denied']);
        }

        $filePath = WRITEPATH . 'uploads/tasks/' . $attachment['file_name'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $attachmentModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Attachment removed.',
        ]);
    }
}

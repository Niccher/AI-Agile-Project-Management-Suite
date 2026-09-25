<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\NoteModel;

class NoteApiController extends BaseController
{
    public function toggleStar($id = null)
    {
        $noteModel = new NoteModel();
        $userId = auth()->id();

        $note = $noteModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$note) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Note not found']);
        }

        $newStatus = $note['is_starred'] ? 0 : 1;
        $noteModel->update($id, ['is_starred' => $newStatus]);

        return $this->response->setJSON(['status' => 'success', 'is_starred' => $newStatus]);
    }

    public function toggleComplete($id = null)
    {
        $noteModel = new NoteModel();
        $userId = auth()->id();

        $note = $noteModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$note) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Note not found']);
        }

        $newStatus = $note['is_completed'] ? 0 : 1;
        $noteModel->update($id, ['is_completed' => $newStatus]);

        return $this->response->setJSON(['status' => 'success', 'is_completed' => $newStatus]);
    }
}

<?php

namespace App\Controllers\User;

use App\Models\NoteModel;
use App\Models\ProjectModel;

class NoteController extends BaseUserController
{
    public function index()
    {
        $noteModel = new NoteModel();
        $projectModel = new ProjectModel();

        $notes = $noteModel->select('notes.*, projects.name as project_name')
            ->join('projects', 'projects.id = notes.project_id', 'left')
            ->where('notes.user_id', $this->userId)
            ->where('notes.is_deleted', 0)
            ->orderBy('notes.is_starred', 'DESC')
            ->orderBy('notes.created_at', 'DESC')
            ->paginate(5, 'notes');

        $data = [
            'user'     => $this->currentUser,
            'notes'    => $notes,
            'pager'    => $noteModel->pager,
            'projects' => $projectModel->where('user_id', $this->userId)->findAll(),
            'stats'    => [
                'total'     => $noteModel->where('user_id', $this->userId)->where('is_deleted', 0)->countAllResults(),
                'starred'   => $noteModel->where('user_id', $this->userId)->where('is_deleted', 0)->where('is_starred', 1)->countAllResults(),
                'completed' => $noteModel->where('user_id', $this->userId)->where('is_deleted', 0)->where('is_completed', 1)->countAllResults(),
                'deleted'   => $noteModel->where('user_id', $this->userId)->where('is_deleted', 1)->countAllResults(),
            ]
        ];

        return view('user/notes', $data);
    }

    public function store()
    {
        $noteModel = new NoteModel();

        $data = $this->request->getPost();
        $data['user_id'] = $this->userId;
        $data['tags'] = json_encode(array_filter(array_map('trim', explode(',', $this->request->getPost('tags') ?? ''))));

        if ($noteModel->insert($data)) {
            return redirect()->to('/notes')->with('message', 'Note created successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $noteModel->errors());
    }

    public function update($id = null)
    {
        $noteModel = new NoteModel();

        $note = $noteModel->where('id', $id)->where('user_id', $this->userId)->first();
        if (!$note) {
            return redirect()->to('/notes')->with('error', 'Note not found.');
        }

        $data = $this->request->getPost();
        if (isset($data['tags'])) {
            $data['tags'] = json_encode(array_filter(array_map('trim', explode(',', $data['tags']))));
        }

        if ($noteModel->update($id, $data)) {
            return redirect()->to('/notes')->with('message', 'Note updated successfully.');
        }

        return redirect()->back()->withInput()->with('errors', $noteModel->errors());
    }

    public function delete($id = null)
    {
        $noteModel = new NoteModel();

        $note = $noteModel->where('id', $id)->where('user_id', $this->userId)->first();
        if (!$note) {
            return redirect()->to('/notes')->with('error', 'Note not found.');
        }

        if ($noteModel->update($id, ['is_deleted' => 1])) {
            return redirect()->to('/notes')->with('message', 'Note deleted.');
        }

        return redirect()->back()->with('error', 'Could not delete note.');
    }
}

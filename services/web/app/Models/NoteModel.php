<?php

namespace App\Models;

use CodeIgniter\Model;

class NoteModel extends Model
{
    protected $table            = 'notes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'project_id',
        'title',
        'content',
        'tags',
        'is_starred',
        'is_completed',
        'is_deleted',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'title'   => 'required|min_length[1]|max_length[255]',
        'user_id' => 'required|numeric',
    ];

    /**
     * Get notes for a user
     */
    public function getNotes(int $userId, bool $includeDeleted = false)
    {
        $builder = $this->where('user_id', $userId);
        
        if (!$includeDeleted) {
            $builder->where('is_deleted', 0);
        }
        
        return $builder->orderBy('is_starred', 'DESC')
                      ->orderBy('created_at', 'DESC')
                      ->findAll();
    }

    /**
     * Get non-deleted notes with project info
     */
    public function getNotesWithProject(int $userId)
    {
        return $this->select('notes.*, projects.name as project_name')
                    ->join('projects', 'projects.id = notes.project_id', 'left')
                    ->where('notes.user_id', $userId)
                    ->where('notes.is_deleted', 0)
                    ->orderBy('notes.is_starred', 'DESC')
                    ->orderBy('notes.created_at', 'DESC')
                    ->findAll();
    }
}

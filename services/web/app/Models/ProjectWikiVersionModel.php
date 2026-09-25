<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectWikiVersionModel extends Model
{
    protected $table            = 'project_wiki_versions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'page_id',
        'version_number',
        'content',
        'change_summary',
        'created_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get revision history with user details
     */
    public function getHistory(int $pageId)
    {
        return $this->select('project_wiki_versions.*, users.username as author_name')
                    ->join('users', 'users.id = project_wiki_versions.created_by', 'left')
                    ->where('page_id', $pageId)
                    ->orderBy('version_number', 'DESC')
                    ->findAll();
    }
}

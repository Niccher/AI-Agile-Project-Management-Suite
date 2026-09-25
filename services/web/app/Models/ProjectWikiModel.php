<?php

namespace App\Models;

use CodeIgniter\Model;

class ProjectWikiModel extends Model
{
    protected $table            = 'project_wiki_pages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'order_index',
        'version',
        'created_by',
        'updated_by',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get pages tree for a project
     */
    public function getTree(int $projectId): array
    {
        $pages = $this->where('project_id', $projectId)
                      ->orderBy('order_index', 'ASC')
                      ->orderBy('title', 'ASC')
                      ->findAll();

        $tree = [];
        $lookup = [];

        foreach ($pages as $p) {
            $p['children'] = [];
            $lookup[$p['id']] = $p;
        }

        foreach ($lookup as $id => &$page) {
            if (!empty($page['parent_id']) && isset($lookup[$page['parent_id']])) {
                $lookup[$page['parent_id']]['children'][] = &$page;
            } else {
                $tree[] = &$page;
            }
        }

        return $tree;
    }

    /**
     * Generate unique slug for project page
     */
    public function generateSlug(int $projectId, string $title, ?int $ignoreId = null): string
    {
        $baseSlug = url_title(strtolower(trim($title)), '-', true);
        if (empty($baseSlug)) {
            $baseSlug = 'page';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $builder = $this->where('project_id', $projectId)->where('slug', $slug);
            if ($ignoreId) {
                $builder->where('id !=', $ignoreId);
            }
            if ($builder->countAllResults() === 0) {
                break;
            }
            $slug = $baseSlug . '-' . (++$counter);
        }

        return $slug;
    }
}

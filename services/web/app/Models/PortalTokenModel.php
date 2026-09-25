<?php

namespace App\Models;

use CodeIgniter\Model;

class PortalTokenModel extends Model
{
    protected $table            = 'project_portal_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'project_id', 'token', 'label', 'expires_at', 'is_active', 'created_by', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Create and return a new portal token
     */
    public function generateToken(int $projectId, int $userId, string $label = '', ?string $expiresAt = null): string
    {
        try {
            $token = bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            $token = md5(uniqid((string)mt_rand(), true));
        }

        $this->insert([
            'project_id' => $projectId,
            'token'      => $token,
            'label'      => !empty($label) ? trim($label) : 'Client Share Link',
            'expires_at' => !empty($expiresAt) ? $expiresAt : null,
            'is_active'  => 1,
            'created_by' => $userId,
        ]);

        return $token;
    }

    /**
     * Find active, non-expired token with project data
     */
    public function findByToken(string $token): ?array
    {
        $now = date('Y-m-d H:i:s');
        
        $row = $this->select('project_portal_tokens.*, projects.name as project_name, projects.description as project_description, projects.status as project_status, projects.progress as project_progress, projects.color as project_color, projects.icon as project_icon, projects.start_date, projects.due_date, projects.tech_stack, projects.categories, projects.updated_at as project_updated_at, users.username as creator_name')
                    ->join('projects', 'projects.id = project_portal_tokens.project_id')
                    ->join('users', 'users.id = project_portal_tokens.created_by', 'left')
                    ->where('project_portal_tokens.token', $token)
                    ->where('project_portal_tokens.is_active', 1)
                    ->where('projects.deleted_at IS NULL')
                    ->groupStart()
                        ->where('project_portal_tokens.expires_at IS NULL')
                        ->orWhere('project_portal_tokens.expires_at >=', $now)
                    ->groupEnd()
                    ->first();

        return $row;
    }

    /**
     * Get all tokens for a given project
     */
    public function getTokensForProject(int $projectId): array
    {
        return $this->where('project_id', $projectId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Revoke / deactivate a token
     */
    public function revokeToken(int $tokenId): bool
    {
        return (bool) $this->update($tokenId, ['is_active' => 0]);
    }
}

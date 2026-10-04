<?php

namespace App\Models;

use CodeIgniter\Model;

class UserInviteModel extends Model
{
    protected $table = 'user_invites';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'email',
        'role',
        'token',
        'status',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateInvite(string $email, string $role = 'user'): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $inviteData = [
            'email'      => strtolower(trim($email)),
            'role'       => $role,
            'token'      => $token,
            'status'     => 'pending',
            'expires_at' => $expiresAt,
        ];

        $id = $this->insert($inviteData);
        $inviteData['id'] = $id;
        $inviteData['invite_link'] = site_url("auth/invite/{$token}");

        return $inviteData;
    }

    public function findValidByToken(string $token): ?array
    {
        return $this->where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }
}

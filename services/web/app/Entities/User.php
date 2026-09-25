<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    protected $attributes = [
        'id'                    => null,
        'username'              => null,
        'first_name'            => null,
        'last_name'             => null,
        'bio'                   => null,
        'avatar'                => null,
        'newsletter'            => false,
        'timezone'              => null,
        'date_format'           => null,
        'preferences'           => null,
        'active'                => 0,
        'activate_hash'         => null,
        'activate_hash_expires' => null,
        'status'                => null,
        'status_message'        => null,
        'force_pass_reset'      => false,
        'permissions'           => null,
        'created_at'            => null,
        'updated_at'            => null,
        'deleted_at'            => null,
    ];

    protected $casts = [
        'id'                    => '?integer',
        'active'                => 'int-bool',
        'permissions'           => 'array',
        'groups'                => 'array',
        'username'              => 'string',
        'first_name'            => 'string',
        'last_name'             => 'string',
        'bio'                   => 'string',
        'avatar'                => 'string',
        'newsletter'            => 'boolean',
        'timezone'              => 'string',
        'date_format'           => 'string',
        'preferences'           => 'json',
        'activate_hash'         => 'string',
        'activate_hash_expires' => 'datetime',
        'status'                => 'string',
        'status_message'        => 'string',
        'force_pass_reset'      => 'boolean',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
    ];

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return (bool) $this->active;
    }

    /**
     * Check if activation token is expired
     */
    public function isActivationExpired(): bool
    {
        if (!$this->activate_hash_expires) {
            return false;
        }

        return strtotime($this->activate_hash_expires) < time();
    }


}
<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User; // Import for return type

class UserModel extends ShieldUserModel
{
    // Your custom validation rules
    protected $allowedFields = [
        'users.username', 'users.status', 'users.status_message', 'users.active', 'users.last_active', 'users.deleted_at',
        'first_name', 'last_name', 'bio', 'avatar', 'newsletter',
        'timezone', 'date_format', 'preferences',
        'reset_hash', 'reset_expires_at',
    ];

    // Disable automatic identity saving since Controller handles it manually
    protected $afterInsert = [];
    protected $afterUpdate = [];

    // Your custom validation rules
    protected $validationRules = [
        'id'            => 'permit_empty|is_natural_no_zero',
        'username'      => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username,id,{id}]',
        'first_name'    => 'required|max_length[50]',
        'last_name'     => 'required|max_length[50]',
    ];


}
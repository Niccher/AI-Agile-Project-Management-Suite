<?php

use App\Models\UserModel;

if (!function_exists('email_exists')) {
    /**
     * Check if email exists in the system (checks auth_identities table)
     *
     * @param string $email
     * @return bool
     */
    function email_exists(string $email): bool
    {
        $db = \Config\Database::connect();

        // Check in auth_identities table (where Shield stores emails)
        $builder = $db->table('auth_identities');
        $result = $builder->where('secret', $email)
            ->where('type', 'email_password')
            ->countAllResults();

        return $result > 0;
    }
}

if (!function_exists('username_exists')) {
    /**
     * Check if username exists
     *
     * @param string $username
     * @return bool
     */
    function username_exists(string $username): bool
    {
        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        return $user !== null;
    }
}

if (!function_exists('get_user_by_email')) {
    /**
     * Get user by email
     *
     * @param string $email
     * @return \App\Entities\User|null
     */
    function get_user_by_email(string $email)
    {
        $userModel = new UserModel();
        return $userModel->where('email', $email)->first();
    }
}
<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'admin' => [
            'title'       => 'System Administrator',
            'description' => 'Complete control of the platform settings, users, and audit logs.',
        ],
        'manager' => [
            'title'       => 'Project Manager',
            'description' => 'Can assign tasks, verify work, and view team reports.',
        ],
        'user' => [
            'title'       => 'Team Member',
            'description' => 'Can execute tasks and log time.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'platform.settings' => 'Can manage system-wide settings',
        'users.manage'      => 'Can create, edit, or delete users and assign roles',
        'audit.view'        => 'Can view the audit log',
        'projects.manage'   => 'Can create, edit, and archive projects',
        'tasks.assign'      => 'Can assign tasks to other users',
        'tasks.verify'      => 'Can approve or reject submitted work',
        'reports.view'      => 'Can view and generate team reports',
        'tasks.execute'     => 'Can update task status and complete tasks',
        'time.log'          => 'Can log time against projects and tasks',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'admin' => [
            'platform.*',
            'users.*',
            'audit.*',
            'projects.*',
            'tasks.*',
            'reports.*',
            'time.*',
        ],
        'manager' => [
            'projects.*',
            'tasks.*',
            'reports.*',
            'time.*',
        ],
        'user' => [
            'tasks.execute',
            'time.log',
        ],
    ];
}

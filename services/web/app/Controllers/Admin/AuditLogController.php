<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuditLogController extends BaseController
{
    public function index()
    {
        // Placeholder until AuditLogModel is created in Phase 3
        return view('admin/audit_logs/index', ['logs' => []]);
    }
}

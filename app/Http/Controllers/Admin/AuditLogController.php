<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AuditLogService;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/AuditLog/Index', [
            'logs' => $this->auditLogService->paginate(),
        ]);
    }
}

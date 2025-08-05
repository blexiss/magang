<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('inventory')->orderBy('created_at', 'desc')->get();
        return view('audit_logs.index', compact('logs'));


    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        try {
            // Try to get logs from DB (latest first)
            $logs = AuditLog::with('inventory')->orderBy('created_at', 'desc')->get();

            // Cache logs as JSON for offline fallback
            Storage::put('audit_cache.json', $logs->toJson());

            Log::info('Audit logs loaded from database successfully.');

            return view('audit_logs.index', ['logs' => $logs]);

        } catch (\Exception $e) {
            Log::error('Failed to load audit logs from DB: ' . $e->getMessage());

            // Fallback: load from cache file
            if (Storage::exists('audit_cache.json')) {
                $json = Storage::get('audit_cache.json');
                $logs = collect(json_decode($json));

                Log::info('Audit logs loaded from cache file.');

                return view('audit_logs.index', [
                    'logs' => $logs,
                    'offline' => true,
                ]);
            } else {
                Log::warning('Audit cache file not found.');

                // No DB, no cache: show empty view or error page
                return view('audit_logs.index', [
                    'logs' => collect(),
                    'offline' => true,
                ]);
            }
        }
    }
}

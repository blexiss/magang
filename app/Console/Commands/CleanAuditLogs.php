<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditLog;
use App\Models\Inventory;

class CleanAuditLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-audit-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes item name and ID from existing audit log actions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logs = AuditLog::all();
        $count = 0;

        foreach ($logs as $log) {
            $item = Inventory::find($log->inventory_id);
            if (!$item) {
                continue;
            }

            // Build regex pattern to match "ItemName (ID: X)" and optional suffix like "'s" or " — "
            $pattern = "/^" . preg_quote($item->name . " (ID: " . $item->id . ")", '/') . "(?:'s| — )/";

            $newAction = preg_replace($pattern, '', $log->action);

            if ($newAction !== $log->action) {
                $this->line("Updating: '{$log->action}' → '{$newAction}'");
                $log->action = $newAction;
                $log->save();
                $count++;
            }
        }

        $this->info("✅ Updated $count audit log entries.");
    }
}

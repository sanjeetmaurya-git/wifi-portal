<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PruneOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune connection logs older than 14 months for government compliance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subMonths(14);
        
        $this->info("Pruning logs older than: " . $cutoffDate->toDateString());

        // 1. Prune radacct (RADIUS logs)
        $deletedRadAcct = DB::table('radacct')
            ->where('acctstarttime', '<', $cutoffDate)
            ->delete();

        // 2. Prune wifi_sessions (App sessions)
        $deletedSessions = DB::table('wifi_sessions')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        // 3. Prune login_logs
        $deletedLogs = DB::table('login_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        Log::info("[Compliance] Pruned $deletedRadAcct RADIUS logs and $deletedSessions application sessions.");
        
        $this->info("Successfully deleted $deletedRadAcct radius logs and $deletedSessions app sessions.");
    }
}

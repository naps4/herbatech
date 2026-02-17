<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CPB;
use Carbon\Carbon;

class CheckCPBOverdue extends Command
{
    protected $signature = 'cpb:check-overdue';
    protected $description = 'Check for overdue CPBs and update status';

    public function handle()
    {
        $this->info('Checking for overdue CPBs...');
        
        $cpbs = CPB::where('status', '!=', 'released')
                   ->where('is_overdue', false)
                   ->get();
        
        $overdueCount = 0;
        
        foreach ($cpbs as $cpb) {
            if ($cpb->checkOverdue()) {
                $overdueCount++;
                $this->info("CPB {$cpb->batch_number} is now overdue.");
            }
        }
        
        $this->info("Found {$overdueCount} overdue CPBs.");
        
        // Log this check
        \Log::info("CPB Overdue Check: " . Carbon::now() . " - Found {$overdueCount} overdue CPBs");
        
        return 0;
    }
}
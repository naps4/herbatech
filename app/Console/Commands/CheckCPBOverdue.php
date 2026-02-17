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
    CPB::where('status', '!=', 'released')
            ->where('is_overdue', false)
            ->chunk(100, function ($cpbs) {
                foreach ($cpbs as $cpb) {
                    if ($cpb->duration_in_current_status > $cpb->time_limit) {
                        $cpb->update(['is_overdue' => true]);
                        // Trigger event untuk notifikasi
                        event(new \App\Events\CPBOverdue($cpb));
                    }
                }
            });
    }
}
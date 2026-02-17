<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\CheckCPBOverdue::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Check for overdue CPBs every hour
        $schedule->command('cpb:check-overdue')->hourly();
        
        // Send daily reports at 8 AM
        $schedule->command('cpb:daily-report')->dailyAt('08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
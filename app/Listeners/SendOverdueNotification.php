<?php

namespace App\Listeners;

use App\Events\CPBOverdue;
use App\Models\Notification;
use App\Models\User;

class SendOverdueNotification
{
    public function handle(CPBOverdue $event)
    {
        // Notify users in the current department
        $users = User::where('role', $event->cpb->status)->get();
        
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'overdue',
                'message' => 'CPB ' . $event->cpb->batch_number . ' telah melebihi batas waktu!',
                'cpb_id' => $event->cpb->id,
                'data' => [
                    'batch_number' => $event->cpb->batch_number,
                    'status' => $event->cpb->status,
                    'overdue_since' => $event->cpb->overdue_since
                ]
            ]);
        }
        
        // Also notify QA
        $qaUsers = User::where('role', 'qa')->get();
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'overdue_alert',
                'message' => 'ALERT: CPB ' . $event->cpb->batch_number . ' overdue di ' . $event->cpb->status,
                'cpb_id' => $event->cpb->id
            ]);
        }
    }
}
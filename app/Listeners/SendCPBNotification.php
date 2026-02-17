<?php

namespace App\Listeners;

use App\Events\CPBCreated;
use App\Models\Notification;
use App\Models\User;

class SendCPBNotification
{
    public function handle(CPBCreated $event)
    {
        // Get QA users
        $qaUsers = User::where('role', 'qa')->get();
        
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'new_cpb',
                'message' => 'CPB baru: ' . $event->cpb->batch_number . ' telah dibuat',
                'cpb_id' => $event->cpb->id,
                'data' => [
                    'batch_number' => $event->cpb->batch_number,
                    'type' => $event->cpb->type,
                    'product_name' => $event->cpb->product_name
                ]
            ]);
        }
    }
}
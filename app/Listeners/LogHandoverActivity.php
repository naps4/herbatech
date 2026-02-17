<?php

namespace App\Listeners;

use App\Events\CPBHandover;
use App\Models\Notification;
use App\Models\HandoverLog;

class LogHandoverActivity
{
    public function handle(CPBHandover $event)
    {
        // Create notification for receiver
        Notification::create([
            'user_id' => $event->toUser->id,
            'type' => 'handover_received',
            'message' => 'Anda menerima CPB: ' . $event->cpb->batch_number . ' dari ' . $event->fromUser->name,
            'cpb_id' => $event->cpb->id,
            'data' => [
                'from_user' => $event->fromUser->name,
                'handover_time' => now()
            ]
        ]);
        
        // Find the latest handover log and update received info
        $handoverLog = HandoverLog::where('cpb_id', $event->cpb->id)
            ->whereNull('received_at')
            ->latest()
            ->first();
            
        if ($handoverLog) {
            $handoverLog->update([
                'received_by' => $event->toUser->id,
                'received_at' => now(),
                'duration_in_hours' => now()->diffInHours($handoverLog->handed_at)
            ]);
        }
    }
}
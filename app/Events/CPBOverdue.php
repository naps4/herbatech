<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\CPB;

class CPBOverdue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cpb;

    public function __construct(CPB $cpb)
    {
        $this->cpb = $cpb;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('cpb.overdue');
    }

    public function broadcastWith()
    {
        return [
            'cpb_id' => $this->cpb->id,
            'batch_number' => $this->cpb->batch_number,
            'status' => $this->cpb->status,
            'message' => 'CPB ' . $this->cpb->batch_number . ' telah melebihi batas waktu!'
        ];
    }
}
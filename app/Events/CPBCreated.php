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

class CPBCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cpb;

    public function __construct(CPB $cpb)
    {
        $this->cpb = $cpb;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('cpb.created');
    }

    public function broadcastWith()
    {
        return [
            'cpb_id' => $this->cpb->id,
            'batch_number' => $this->cpb->batch_number,
            'message' => 'CPB baru telah dibuat: ' . $this->cpb->batch_number
        ];
    }
}
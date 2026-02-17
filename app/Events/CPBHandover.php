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
use App\Models\User;

class CPBHandover implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $cpb;
    public $fromUser;
    public $toUser;

    public function __construct(CPB $cpb, User $fromUser, User $toUser)
    {
        $this->cpb = $cpb;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('cpb.handover.' . $this->toUser->id);
    }

    public function broadcastWith()
    {
        return [
            'cpb_id' => $this->cpb->id,
            'batch_number' => $this->cpb->batch_number,
            'from_user' => $this->fromUser->name,
            'message' => 'CPB ' . $this->cpb->batch_number . ' telah diserahkan kepada Anda'
        ];
    }
}
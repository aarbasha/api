<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ToogleBlocke implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $blockedUser;
    public $Auth;
    public $type;


    public function __construct($id, $blockedUser, $type)
    {
        $this->id = $id;
        $this->blockedUser = $blockedUser;
        $this->type = $type;
        $this->Auth = Auth::user()->id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Chats' . $this->id);
    }

    public function broadcastWith()
    {
        return [
            'block_id' =>   $this->id,
            'user_block' =>   $this->blockedUser,
            'Auth_id' =>   $this->Auth,
            'type' =>    $this->type,
        ];
    }
}

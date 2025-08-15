<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypeingUser implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public  $sender;
    public  $isTyping;
    public  $off_typing;

    public function __construct($sender, $isTyping, $off_typing)
    {
        $this->sender = $sender;
        $this->isTyping = $isTyping;
        $this->off_typing = $off_typing;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Chats' . $this->sender);
    }

    public function broadcastWith()
    {
        return [
            'sender' =>   $this->sender,
            'isTyping' =>   $this->isTyping,
            'off_typing' =>   $this->off_typing
        ];
    }
}

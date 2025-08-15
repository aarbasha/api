<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMassage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */


    public $receiverModel;
    public $chat;
    public $channelName;
    public $channelId;


    public function __construct($receiverModel, $chat, $channelName, $channelId)
    {
        $this->receiverModel = $receiverModel;
        $this->chat = $chat;
        $this->channelName = $channelName;
        $this->channelId = $channelId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Chats' . $this->chat->receiver);
    }

    public function broadcastWith()
    {
        return [
            'user' =>   $this->receiverModel,
            'chat' =>   $this->chat,
            'channelName' =>   $this->channelName,
            'channelId' =>   $this->channelId
        ];
    }
}

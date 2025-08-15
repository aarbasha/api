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

class RemoveFavorite implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post_id;
    public $Auth;
    public $type;
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
        $this->Auth = Auth::user();
        $this->type = 'remove';
    }

    public function broadcastOn()
    {
        return new Channel('ToggleFavorite');
    }
}

<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Post $post) {}

    public function broadcastOn(): array
    {
        return [new PresenceChannel('feed')];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->post->id,
            'body' => $this->post->body,
            'author' => $this->post->user->name,
            'created_at' => $this->post->created_at->toIso8601String(),
        ];
    }
}

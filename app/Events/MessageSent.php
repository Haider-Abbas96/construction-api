<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat\Message;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $message;
    public $conversationId;
    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, int $conversationId)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;
        
        // Don't serialize these relationships (they'll be loaded separately)
        $this->message->setRelations([]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Changed to use conversationId in channel name
            new PrivateChannel('chat.'.$this->conversationId),
        ];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'conversation_id' => $this->message->conversation_id,
                'user_id' => $this->message->user_id,
                'created_at' => $this->message->created_at->toDateTimeString(),
                'updated_at' => $this->message->updated_at->toDateTimeString(),
            ],
            'sender' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                // Add other user fields you need
            ]
        ];
    }
}

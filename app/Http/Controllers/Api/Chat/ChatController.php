<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Get all conversations for the logged-in user
    public function conversations()
    {
        $user = Auth::user();

        $conversations = $user->conversations()
            ->with(['users:id,name', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        return response()->json($conversations);
    }

    // Get messages in a conversation
    public function messages($conversationId)
    {
        $conversation = Conversation::with('messages.user')->findOrFail($conversationId);

        // Check if user belongs to conversation
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($conversation->messages);
    }

    // Send a message
    public function sendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
          'body' => 'required|string',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        }

        $conversation = Conversation::findOrFail($conversationId);

        if (!$conversation->users->contains(Auth::id())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        broadcast(new MessageSent($message, $conversationId))->toOthers();

        return response()->json($message);
    }

    // Create new conversation between two users (if it doesn't exist)
    public function startConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        }

        $user1 = Auth::id();
        $user2 = $request->user_id;

        // Check if conversation already exists between these users
        $conversation = Conversation::whereHas('users', function ($q) use ($user1) {
                $q->where('user_id', $user1);
            })->whereHas('users', function ($q) use ($user2) {
                $q->where('user_id', $user2);
            })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'type' => 'private'
            ]);

            $conversation->users()->attach([$user1, $user2]);
        }

        return response()->json($conversation->load('users:id,name'));
    }
}

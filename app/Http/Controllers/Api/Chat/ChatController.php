<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Models\User;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Get all conversations for the logged-in user
    public function conversations()
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Unauthorized user.',
                ], 401);
            }
    
            $conversations = $user->conversations()
                ->with('users:id,name') // only load users, not messages
                ->get();
    
            return response()->json([
                'status' => 200,
                'message' => 'Conversations fetched successfully.',
                'data' => $conversations,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong while fetching conversations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        $conversation = Conversation::find($request->conversation_id);
        
        if (!$conversation) {
            return response()->json([
                'status' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => auth()->id(),
            'body' => $request->body,
        ]);

        // Create message status for each recipient (excluding sender)
        foreach ($conversation->users as $user) {
            if ($user->id !== Auth::id()) {
                $message->statuses()->create([
                    'user_id' => $user->id,
                    'status' => 'delivered',
                ]);
            }
        }

        broadcast(new MessageSent($message, $request->conversation_id))->toOthers();

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }
    

    public function messages($conversationId)
    {
        $conversation = Conversation::with([
            'users:id,name',
            'messages' => function ($query) {
                $query->with('user:id,name')  // Load message sender's info
                      ->orderBy('created_at', 'asc'); // Optional: order oldest first
            }
        ])->find($conversationId);
    
        if (!$conversation) {
            return response()->json([
                'status' => 404,
                'message' => 'Conversation not found',
            ], 404);
        }
    
        // Ensure the authenticated user is a participant
        if (!$conversation->users->contains('id', Auth::id())) {
            return response()->json([
                'status' => 403,
                'message' => 'Unauthorized access to this conversation',
            ], 403);
        }
    
        return response()->json([
            'status' => 200,
            'message' => 'Messages retrieved successfully',
            'data' => $conversation->messages,
        ]);
    }
    

    // Create new conversation between two users (if it doesn't exist)
    public function startConversation(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()->all(),
                ], 422);
            }
    
            $user1 = Auth::id();
            $user2 = $request->user_id;
    
            if ($user1 == $user2) {
                return response()->json([
                    'status' => 400,
                    'message' => 'You cannot start a conversation with yourself.',
                ], 400);
            }
    
            // Check if conversation already exists between these two users
            $conversation = Conversation::whereHas('users', function ($q) use ($user1) {
                    $q->where('user_id', $user1);
                })->whereHas('users', function ($q) use ($user2) {
                    $q->where('user_id', $user2);
                })->first();
    
            if (!$conversation) {
                $conversation = Conversation::create([
                    'type' => 'private',
                ]);
    
                $conversation->users()->attach([$user1, $user2]);
    
                return response()->json([
                    'status' => 201,
                    'message' => 'New conversation started.',
                    'data' => $conversation->load('users:id,name'),
                ], 201);
            }
    
            return response()->json([
                'status' => 200,
                'message' => 'Conversation already exists.',
                'data' => $conversation->load('users:id,name'),
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to start conversation.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

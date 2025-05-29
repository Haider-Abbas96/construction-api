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
                ->with([
                    'users:id,name',
                    'messages' => function ($q) {
                        $q->latest()->limit(1);
                    }
                ])
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

    public function messages($conversationId)
    {
        $conversation = Conversation::with(['messages.user', 'users:id,name'])->find($conversationId);
    
        if (!$conversation) {
            return response()->json([
                'status' => 404,
                'message' => 'Conversation not found',
            ], 404);
        }
    
        // Check if authenticated user is part of the conversation
        if (!$conversation->users->contains(Auth::id())) {
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
    

    // Send a message
    public function sendMessage(Request $request, $conversationId)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()->all(),
            ], 422);
        }
    
        $conversation = Conversation::with('users')->find($conversationId);
    
        if (!$conversation) {
            return response()->json([
                'status' => false,
                'message' => 'Conversation not found',
            ], 404);
        }
    
        if (!$conversation->users->contains(Auth::id())) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access to this conversation',
            ], 403);
        }
    
        $message = Message::create([
            'conversation_id' => $conversationId,
            'user_id' => Auth::id(),
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

        broadcast(new MessageSent($message, $conversationId))->toOthers();
    
        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
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

    public function markAsRead($messageId)
    {
        $userId = Auth::id();

        $messageStatus = \App\Models\Chat\MessageStatus::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->first();

        if (!$messageStatus) {
            return response()->json([
                'status' => false,
                'message' => 'Message status not found or not your message',
            ], 404);
        }

        if ($messageStatus->status === 'read') {
            return response()->json([
                'status' => true,
                'message' => 'Message already marked as read',
            ]);
        }

        $messageStatus->update(['status' => 'read']);

        return response()->json([
            'status' => true,
            'message' => 'Message marked as read',
        ]);
    }

}

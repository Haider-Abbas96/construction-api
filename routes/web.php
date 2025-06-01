<?php

use Illuminate\Support\Facades\Route;
use App\Models\Chat\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-broadcast', function () {
    // Assume a message with ID 1 exists and belongs to conversation ID 1
    $message = Message::with('user')->latest()->first(); // Make sure this message exists

    if (!$message) {
        return 'Message not found';
    }

    broadcast(new MessageSent($message, $message->conversation_id))->toOthers();

    return 'Broadcast fired successfully.';
});

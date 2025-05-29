<?php


use App\Http\Controllers\Api\Chat\ChatController;
use App\Http\Controllers\Api\Contractor\MaterialController;
use App\Http\Controllers\Api\Recipient\BookingController;
use App\Models\Recipient\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Models\Chat\Conversation;
use App\Models\Chat\Message;
use App\Events\MessageSent;
use App\Http\Controllers\Api\Auth\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix("v1")->group(function(){
      // routes for authorization
      Route::controller(AuthController::class)->group(function(){
        Route::post("register","register")->name("register");
        Route::post("login","login")->name("login");
        Route::get('logout',  'logout')->name("logout");
    });

    Route::middleware(["auth:api"])->group(function(){
        Route::controller(ProfileController::class)->group(function(){
            Route::post("profile/update","updateProfile");
            Route::get("profile/info","profileInfo");
            Route::get("profile/deleteAccount","deleteAccount");
            Route::post("profile/change-password","changePassword");
            Route::get("MaterialTypes/all","showMaterialTypes");
        });

        Route::controller(BookingController::class)->prefix("booking")->group(function(){
            Route::get('/all',  'index');
            Route::get('show/{id}', 'show');
            Route::post('cancel/{id}', 'bookingCancellation');
        });

        Route::controller(ChatController::class)->prefix("chat")->group(function(){
            Route::get('/conversations', 'conversations');
            Route::post('/conversations',  'startConversation');
            Route::get('/conversations/messages/{id}',  'messages');
            Route::post('/conversations/messages/{conversationId}', 'sendMessage');
            Route::post('/conversations/read/{id}',  'markAsRead');
        });
   

        Route::post('/test-broadcast', function() {
            $conversation = Conversation::first();
            $message = Message::first();
            
            broadcast(new MessageSent($message, $conversation->id));
            
            return response()->json(['status' => 'Message sent']);
        });

    });

    Route::middleware(["auth:api","contractor"])->prefix("contractor")->group(function(){
        Route::controller(MaterialController::class)->prefix("materials")->group(function(){
            Route::get('/all',  'index');
            Route::post('store',  'store');
            Route::get('show/{id}', 'show');
            Route::put('update/{id}',  'update');
            Route::delete('delete/{id}',  'delete');
        });
    });

    Route::middleware(["auth:api","recipient"])->prefix("recipient")->group(function(){
        Route::controller(MaterialController::class)->prefix("materials")->group(function(){
            Route::get('/all',  'index');
            Route::get('show/{id}', 'show');
        });

        Route::controller(BookingController::class)->prefix("booking")->group(function(){
            Route::post('store',  'store');
        });
    });


});

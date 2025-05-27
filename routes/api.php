<?php


use App\Http\Controllers\Api\Contractor\MaterialController;
use App\Http\Controllers\Api\Recipient\BookingController;
use App\Models\Recipient\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\ProfileController;
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
            Route::get("MaterialTypes/all","showMaterialTypes");
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
            Route::get('/all',  'index');
            Route::post('store',  'store');
        });
    });
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix("v1")->group(function(){
      // routes for authorization
      Route::controller(AuthController::class)->group(function(){
        Route::post("register","register")->name("register");
        Route::post("login","login")->name("login");
        Route::post('logout',  'logout')->name("logout");
    });
});

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'email'             => 'required|string|email|max:255|unique:users',
            'password'          => 'required|string|min:6',
            'role'              => 'required|in:contractor,recipient',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        }

        // Create the new user
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash the password for security
            "role" =>$request->role  //default role is user
        ]);
        // Return a success response with the user data and JWT token
        return response()->json([
            'message' => 'User successfully registered',
            'data' => $user,
        ], 201);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            "password" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        } else {
            $credentials = $request->only('email', 'password');
            if (!$token = Auth::guard('api')->attempt($credentials)) {
                return response()->json([
                    "status" => 401,
                    "message" => "Incorrect email or password",
                    'error' => 'Unauthorized access'
                ], 401);
            }
            $user = Auth::guard('api')->user();
            $role = $user->role_id == 1 ? 'admin' : 'user';
            return response()->json([
                "status" => 200,
                "message" => "You are logged in",
                'token' => $token,
                "user" =>  $user,
                "role"=> $role
            ], 200);
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api\Recipient;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Contractor\Material;
use Illuminate\Support\Facades\Validator;
use App\Models\Recipient\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'material_id' => 'required|exists:materials,id',
            'unit' => 'required|string',
            'total_price' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        } 
        $materialName = Material::where("id",$request->material_id)->name;
        dd($materialName);
        $booking = Booking::create([
            'material_id' => $request->material_id,
            'user_id' => $user->id,
            'unit' => $request->unit,
            'total_price' => $request->total_price,
        ]);
        try {
            Mail::send('emails.email_verification', [
                'user' => $user,
                "total_price" => $booking->total_price,
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Welcome to Fitness Portal');
            });
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Material booked successfully',
            'data' => $booking,
        ], 201);
    }
}

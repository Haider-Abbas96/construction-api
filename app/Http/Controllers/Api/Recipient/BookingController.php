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

    public function index(){
        $user = Auth::user();
        $bookings = Booking::where("user_id",$user->id)->with('material')->latest()->get();
        return response()->json([
            'status' => 200,
            'message' => 'User bookings retrieved successfully',
            'data' => $bookings,
        ]);
    }
    public function store(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'material_id'   => 'required|exists:materials,id',
            'unit'          => 'required|string|min:1',
            'quantity'      => 'required|integer|min:1',
            'booking_type'  => 'required|string|max:255',
            'address'       => 'nullable|string|max:500',
            'time'          => 'required|date_format:H:i',
            'date'          => 'required|date|after_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        } 
        $material = Material::findOrFail($request->material_id);
        $materialName = $material->name;
        $materialOwner = $material->user;
        if (!$materialOwner) {
            return response()->json([
                "status" => 404,
                "message" => "Material owner not found.",
            ], 404);
        }
        // Calculate total price = unit * material.price
        $totalPrice = $request->quantity * $material->price;
        // Create booking
        $booking = Booking::create([
            'material_id'   => $request->material_id,
            'user_id'       => $user->id,
            "material_name" => $materialName,
            'unit'          => $request->unit,
            'quantity'      => $request->quantity,
            'booking_type'  => $request->booking_type,
            'address'       => $request->address,
            'time'          => $request->time,
            'date'          => $request->date,
            'total_price'   => $totalPrice,
            'status'        => 'pending', // default
        ]);
        try {
      
            Mail::send('emails.booking_success', [
                'user' => $user,
                'booking' => $booking,
                'material' => $material,
                "materialOwner" => $materialOwner 
            ], function ($message) use ($materialOwner) {
                $message->to($materialOwner->email)
                    ->subject(' New Booking Received');
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

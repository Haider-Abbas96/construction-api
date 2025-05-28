<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function profileInfo(){
        $user = Auth::guard('api')->user();
        if(!$user){
            return response()->json([
                'status' => '404',
                "message" => "user not found"
            ],404);  
        }
        return response()->json([
            'status' => 200,
            'user' => $user
        ],200); 
    }

    public function updateProfile(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'nullable|in:recipient,contractor,admin',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'who_donating' => 'nullable|string|max:255',
            'who_recipienting' => 'nullable|string|max:255',
            'recipient_type' => 'nullable|string|max:255',
            'material_type_id' => 'nullable|exists:material_types,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
         // Update fields conditionally
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        if ($request->filled('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        if ($request->filled('role')) {
            $user->role = $request->role;
        }

        if ($request->filled('company_name')) {
            $user->company_name = $request->company_name;
        }

        if ($request->filled('company_address')) {
            $user->company_address = $request->company_address;
        }

        if ($request->filled('who_donating')) {
            $user->who_donating = $request->who_donating;
        }

        if ($request->filled('who_recipienting')) {
            $user->who_recipienting = $request->who_recipienting;
        }

        if ($request->filled('recipient_type')) {
            $user->recipient_type = $request->recipient_type;
        }

        if ($request->filled('material_type_id')) {
            $user->material_type_id = $request->material_type_id;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function deleteAccount(){
        $user=Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized user.'
            ], 401);
        }
        DB::beginTransaction();
        try {
            // Delete the user
            $user->delete();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Your account and all related data have been deleted successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            DB::rollBack(); // Important in case something fails
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting the account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showMaterialTypes(){
        $materialTypes = DB::table('material_types')->get();
        if ($materialTypes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No material types found.',
                'data' => []
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Material types fetched successfully.',
            'data' => $materialTypes
        ]);
    }

    public function changePassword(Request $request){
        $user=Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized user.'
            ], 401);
        }
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|different:old_password',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
            // Check if old password matches
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password does not match.'
            ], 403);
        }

            // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.'
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api\Contractor;

use App\Http\Controllers\Controller;
use App\Models\Contractor\Material;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $materials = Material::where('user_id', $user->id)->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Materials fetched successfully.',
            'data' => $materials
        ], 200);
    }

     // Store a new material
     public function store(Request $request)
     {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'material_type_id' => 'required|exists:material_types,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'unit' => 'required|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            "total_price" => "nullable|numeric|min:0",
            'quantity' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
 
         if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        } 
         $imagePath = null;
         if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
        
            // Save directly inside public/profile
            $file->move(public_path('construction-image/material-images'), $filename);
        
            $imagePath = 'construction-image/material-images/' . $filename;
        }

        $material = Material::create([
            "user_id" => $user->id,
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imagePath,
            "material_type_id" => $request->material_type_id,
            "unit" => $request->unit,
            "price_per_unit" => $request->price_per_unit,
            "quantity" => $request->quantity ,
            "total_price" => $request->total_price,
        ]);
 
         return response()->json([
             'success' => true,
             'message' => 'Material created successfully.',
             'data' => $material
         ], 201);
     }

      // Show a single material
    public function show($id)
    {
        $user = Auth::user();
        $material = Material::where('user_id', $user->id)->find($id);

        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Material details fetched.',
            'data' => $material
        ], 200);
    }

    public function showMaterial($id){
        $material = Material::find($id);

        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Material details fetched.',
            'data' => $material
        ], 200);
    }

     // Update a material
     public function update(Request $request, $id)
     {
        $user = Auth::user();
        $material = Material::where('user_id', $user->id)->find($id);
 
        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found.'
            ], 404);
        }
 
        $validator = Validator::make($request->all(), [
            'material_type_id' => 'nullable|exists:material_types,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            "total_price" => "nullable|numeric|min:0",
            'quantity' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                "status" => 422,
                "message" => "something went wrong! Validation failed",
                "errors" => $validator->errors()->all(),
            ], 422);
        }
        if ($request->filled('material_type_id')) {
            $material->material_type_id = $request->material_type_id;
        }
        if ($request->filled('name')) {
            $material->name = $request->name;
        } 
        if ($request->filled('unit')) {
            $material->unit = $request->unit;
        } 
        if ($request->filled('price_per_unit')) {
            $material->price_per_unit = $request->price_per_unit;
        } 
        if ($request->filled('total_price')) {
            $material->total_price = $request->total_price;
        } 
        if ($request->filled('quantity')) {
            $material->quantity = $request->quantity;
        } 
        if ($request->filled('description')) {
            $material->description = $request->description;
        } 

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($material->image && file_exists(public_path($material->image))) {
                unlink(public_path($material->image));
            }
    
            // Upload new image
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('construction-image/material-images'), $filename);
    
            $material->image = 'construction-image/material-images/' . $filename;
        }
 
         $material->save();
 
         return response()->json([
             'success' => true,
             'message' => 'Material updated successfully.',
             'data' => $material
         ], 200);
     }
 
     // Delete a material
    public function delete($id)
    {
        $user = Auth::user();
        $material = Material::where('user_id', $user->id)->find($id);
 
        if (!$material) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found.'
            ], 404);
        }
 
        if ($material->image && file_exists(public_path($material->image))) {
            unlink(public_path($material->image));
        }
 
        $material->delete();
 
        return response()->json([
            'success' => true,
            'message' => 'Material deleted successfully.'
        ], 200);
    }

    public function allMaterials(){
        $materials = Material::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Materials fetched successfully.',
            'data' => $materials
        ], 200);
    }

}

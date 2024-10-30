<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SquareFootageBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSquareFootageBillsController extends Controller
{
    public function store(Request $request)
    {

        if ($request->ajax()) {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'image_path' => 'required|mimes:png,jpg,webp|max:1024',
                'wager_name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                'multiplier' => 'required|numeric|min:0',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again!'], 422);
            }

            $image_path = null;

            // Process the image if it exists
            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
            }

            try {
                // Create the square footage bill
                SquareFootageBill::create([
                    'image_path' => $image_path,
                    'wager_name' => $request->wager_name,
                    'price' => $request->price,
                    'type' => $request->type,
                    'multiplier' => $request->type === 'full_contract' ? 1 : $request->multiplier,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                ]);

                return response()->json(['message' => 'Square footage bill created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}

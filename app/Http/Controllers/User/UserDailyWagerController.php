<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserDailyWagerController extends Controller
{
    public function store(Request $request)
    {

        if ($request->ajax()) {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'price_per_day' => 'required|integer',
                'wager_name' => 'required|string|max:255',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again!'], 422);
            }

            try {
                // Create the daily wager
                DailyWager::create($request->all());

                return response()->json(['message' => 'Wager created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}

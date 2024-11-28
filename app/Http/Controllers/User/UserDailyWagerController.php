<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UserDailyWagerController extends Controller
{
    public function store(Request $request)
    {
        if ($request->ajax()) {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'price_per_day' => 'required|numeric|max:9999999999',
                'wager_name' => 'required|string|max:255',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Form Fields Are Missing...'], 422);
            }

            try {
                // Create the daily wager
                $daily_wager = DailyWager::create([
                    'price_per_day' => $request->price_per_day,
                    'wager_name' => $request->wager_name,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                    // 'verified_by_admin' => 0,
                ]);


                $data = [
                    'user' => auth()->user()->name,
                    'item' => $daily_wager->wager_name
                ];

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Wager created successfully.'], 201);
            } catch (\Exception $e) {
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}

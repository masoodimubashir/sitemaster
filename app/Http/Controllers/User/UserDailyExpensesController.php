<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyExpenses;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UserDailyExpensesController extends Controller
{
    public function store(Request $request)
    {
        if ($request->ajax()) {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'item_name' => 'required|string|max:255',
                'price' => 'required|numeric|max:9999999999',
                'bill_photo' => 'required|image|mimes:jpg,jpeg,webp|max:1024',
                'phase_id' => 'required|exists:phases,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Form Fields Are Missing...'], 422);
            }

            $image_path = null;

            // Process the image if it exists
            if ($request->hasFile('bill_photo')) {
                $image_path = $request->file('bill_photo')->store('Expenses', 'public');
            }

            try {
                // Create the daily expenses entry
                $expense = DailyExpenses::create([
                    'bill_photo' => $image_path,
                    'item_name' => $request->item_name,
                    'price' => $request->price,
                    'phase_id' => $request->phase_id,
                    'user_id' => auth()->user()->id,
                    'verified_by_admin' => 0,
                ]);


                $data = [
                    'user' => auth()->user()->name,
                    'item' => $expense->item_name
                ];

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Expenses detail created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }
}

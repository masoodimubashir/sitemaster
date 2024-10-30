<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\User;
use App\Models\WagerAttendance;
use Illuminate\Http\Request;

class UserWagerAttendanceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function __invoke(Request $request)
    {
        if ($request->ajax()) {
            try {

                $request->validate([
                    'no_of_persons' => 'required|integer',
                    'daily_wager_id' => 'required|exists:daily_wagers,id',
                    'is_present' => 'sometimes'
                ]);

                $daily_wager_attendance = new WagerAttendance();

                $daily_wager_attendance->no_of_persons = $request->no_of_persons;
                $daily_wager_attendance->daily_wager_id = $request->daily_wager_id;
                $daily_wager_attendance->user_id = auth()->user()->id;
                $daily_wager_attendance->is_present = $request->is_present ? 1 : 0;

                $daily_wager_attendance->save();



                return response()->json(['success', 'attendance done...'], 200);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors' => $e->validator->errors()], 422);
            }
        }
    }
}

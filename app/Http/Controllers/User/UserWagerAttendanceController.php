<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WagerAttendance;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UserWagerAttendanceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {
            // Validation rules
            $validator = Validator::make($request->all(), [
                'no_of_persons' => 'required|integer|min:1',
                'daily_wager_id' => 'sometimes|exists:daily_wagers,id',
                'date' => 'sometimes',
                'phase_id' => 'required|exists:phases,id'
            ]);


            if ($validator->fails()) {
                return response()->json(['errors' => 'Form Fields Are Missing'], 422);
            }

            try {

                $daily_wager_attendance = new WagerAttendance();
                $daily_wager_attendance->no_of_persons = $request->no_of_persons;
                $daily_wager_attendance->daily_wager_id = $request->daily_wager_id;
                $daily_wager_attendance->user_id = auth()->user()->id;
                $daily_wager_attendance->is_present = 1;
                $daily_wager_attendance->created_at = $request->date ? $request->date : now();
                $daily_wager_attendance->phase_id = $request->phase_id;
                $daily_wager_attendance->verified_by_admin = 0;

                $daily_wager_attendance->save();


                $data = [
                    'user' => auth()->user()->name,
                    'item' => $daily_wager_attendance->dailyWager->wager_name
                ];

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Attendance recorded successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $wager_id = base64_decode($id);

        $daily_wager_attendance = WagerAttendance::with('phase.site')->findorFail($wager_id);

        return view('profile.partials.Admin.WagerAttendance.edit-wager-attendance', compact('daily_wager_attendance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'no_of_persons' => 'required|integer|min:1',
            'daily_wager_id' => 'sometimes|exists:daily_wagers,id',
            'date' => 'sometimes',
        ]);

        $wager_attendance = WagerAttendance::find($id);

        $wager_attendance->no_of_persons = $request->no_of_persons;
        $wager_attendance->daily_wager_id = $request->daily_wager_id;
        $wager_attendance->user_id = auth()->user()->id;
        $wager_attendance->is_present =  1;
        $wager_attendance->created_at = $request->date ? $request->date :  now();
        $wager_attendance->save();

        return redirect()->route('user.sites.show', [base64_encode($wager_attendance->phase->site->id)])
            ->with('status', 'update');
    }

}

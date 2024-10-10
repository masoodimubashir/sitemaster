<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\User;
use App\Models\WagerAttendance;
use Illuminate\Http\Request;

class WagerAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $user = User::with('wagerAttendances')->find(auth()->user()->id);

        $wagers = DailyWager::orderBy('wager_name')->get();

        return view('profile.partials.Admin.WagerAttendance.wager-attendance', compact('wagers', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {

            try {

                $request->validate([
                    'no_of_persons' => 'required|integer',
                    'daily_wager_id' => 'sometimes|exists:daily_wagers,id',
                    'date' => 'required|date',
                    'phase_id' => 'required|exists:phases,id'
                ]);

                $daily_wager_attendance = new WagerAttendance();

                $daily_wager_attendance->no_of_persons = $request->no_of_persons;
                $daily_wager_attendance->daily_wager_id = $request->daily_wager_id;
                $daily_wager_attendance->user_id = auth()->user()->id;
                $daily_wager_attendance->is_present = 1;
                $daily_wager_attendance->created_at = $request->date;
                $daily_wager_attendance->phase_id = $request->phase_id;

                $daily_wager_attendance->save();

                return response()->json(['message', 'attendance done...'], 201);

            } catch (\Illuminate\Validation\ValidationException $e) {

                return response()->json(['errors' => $e->validator->errors()], 422);

            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
            'no_of_persons' => 'required|integer',
            'daily_wager_id' => 'sometimes|exists:daily_wagers,id',
            'date' => 'required|date',
            'is_present' => 'required'
        ]);

        $wager_attendance = WagerAttendance::find($id);

        $wager_attendance->no_of_persons = $request->no_of_persons;
        $wager_attendance->daily_wager_id = $request->daily_wager_id;
        $wager_attendance->user_id = auth()->user()->id;
        $wager_attendance->is_present = $request->is_present ? 1 : 0;
        $wager_attendance->created_at = $request->date;

        $wager_attendance->save();

        return redirect()->back()->with('success', 'attendance done...');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $daily_wager_attendance_id = base64_decode($id);

        $daily_wager_attendance = WagerAttendance::find($daily_wager_attendance_id);

        $daily_wager_attendance->delete();

        return redirect()->back()->with('success', 'wager attendance deleted...');
    }
}

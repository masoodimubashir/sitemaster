<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\User;
use App\Models\WagerAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

            dd($request->all());


            $validator = Validator::make($request->all(), [
                'no_of_persons' => 'required|integer|min:1',
                'daily_wager_id' => 'sometimes|exists:daily_wagers,id',
                'date' => 'sometimes',
                'phase_id' => 'required|exists:phases,id'
            ]);

            if ($validator->fails()) {

                return response()->json(['errors' => 'Validation Error... Try Again!'], 422);
            }

            try {

                $daily_wager_attendance = new WagerAttendance();
                $daily_wager_attendance->no_of_persons = $request->no_of_persons;
                $daily_wager_attendance->daily_wager_id = $request->daily_wager_id;
                $daily_wager_attendance->user_id = auth()->user()->id;
                $daily_wager_attendance->is_present = 1;
                $daily_wager_attendance->created_at = $request->date ? $request->date : now();
                $daily_wager_attendance->phase_id = $request->phase_id;
                $daily_wager_attendance->verified_by_admin = 1;

                $daily_wager_attendance->save();

                return response()->json(['message' => 'Attendance recorded successfully.'], 201);

            } catch (\Exception $e) {
                
                return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
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

        return redirect()->route('sites.show', [base64_encode($wager_attendance->phase->site->id)])
            ->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {


        try {

            $daily_wager_attendance = WagerAttendance::find($id);


            $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($daily_wager_attendance) {
                $query->where('site_id', $daily_wager_attendance->phase->site_id)
                    ->orWhere('supplier_id', $daily_wager_attendance->dailyWager->supplier->supplier_id);
            })->exists();

            if ($hasPaymentRecords) {
                return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
            }

            $daily_wager_attendance->delete();

            return response()->json(['message' => 'Item Deleted...'], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
        }
    }
}

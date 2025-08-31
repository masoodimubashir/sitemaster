<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceSetup;
use App\Models\Wager;
use App\Models\Wasta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendnaceSetupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {


            $validated = $request->validate([
                'worker_type' => 'required|in:wasta,wager,multiple',
                'wasta_name' => 'required_if:worker_type,wasta|string|max:255',
                'wasta_price' => 'required_if:worker_type,wasta|numeric|min:0',
                'wager_name' => 'required_if:worker_type,wager|string|max:255',
                'wager_price' => 'required_if:worker_type,wager|numeric|min:0',
                'wager_wasta_id' => 'nullable|exists:wastas,id',
                'multiple_names' => 'required_if:worker_type,multiple|array|min:1',
                'multiple_names.*' => 'string|max:255',
                'multiple_prices' => 'required_if:worker_type,multiple|array|min:1',
                'multiple_prices.*' => 'numeric|min:0',
                'multiple_counts' => 'required_if:worker_type,multiple|array|min:1',
                'multiple_counts.*' => 'numeric|min:1',
                'multiple_wasta_id' => 'nullable|exists:wastas,id',
                'attendance_date' => 'required|date',
                'site_id' => 'required|exists:sites,id',
            ]);


            $records = [];

            if ($validated['worker_type'] === 'wasta') {
                $wasta = Wasta::firstOrCreate(
                    [
                        'wasta_name' => trim(strtolower($validated['wasta_name']))
                    ],
                    [
                        'price' => $validated['wasta_price'],
                        'contact_no' => null
                    ]
                );

                $setup = AttendanceSetup::create([
                    'name' => $wasta->wasta_name,
                    'count' => 1,
                    'price' => $validated['wasta_price'],
                    'site_id' => $validated['site_id'],
                    'setupable_type' => Wasta::class,
                    'setupable_id' => $wasta->id,
                    'created_at' => $validated['attendance_date']

                ]);

                $attendance = Attendance::create([
                    'attendance_date' => $validated['attendance_date'],
                    'attendance_setup_id' => $setup->id,
                    'is_present' => 1,
                    'created_at' => $validated['attendance_date']
                ]);

                $records[] = $attendance;
            } elseif ($validated['worker_type'] === 'wager') {
                $wager = Wager::firstOrCreate(
                    [
                        'wager_name' => trim(strtolower($validated['wager_name'])),
                        'wasta_id' => $validated['wager_wasta_id'] ?? null,
                    ],
                    [
                        'price' => $validated['wager_price'],
                    ]
                );

                $setup = AttendanceSetup::create([
                    'name' => $wager->wager_name,
                    'count' => 1,
                    'price' => $validated['wager_price'],
                    'site_id' => $validated['site_id'],
                    'setupable_type' => Wager::class,
                    'setupable_id' => $wager->id,
                    'created_at' => $validated['attendance_date']
                ]);

                $attendance = Attendance::create([
                    'attendance_date' => $validated['attendance_date'],
                    'attendance_setup_id' => $setup->id,
                    'is_present' => 1,
                    'created_at' => $validated['attendance_date']

                ]);

                $records[] = $attendance;
            } else {
                $wastaId = $validated['multiple_wasta_id'] ?? null;

                foreach ($validated['multiple_names'] as $index => $name) {
                    $wager = Wager::firstOrCreate(
                        [
                            'wager_name' => trim(strtolower($name)),
                            'wasta_id' => $wastaId,
                        ],
                        [
                            'price' => $validated['multiple_prices'][$index],
                        ]
                    );

                    $setup = AttendanceSetup::create([
                        'name' => $wager->wager_name,
                        'count' => $validated['multiple_counts'][$index],
                        'price' => $validated['multiple_prices'][$index],
                        'site_id' => $validated['site_id'],
                        'setupable_type' => Wager::class,
                        'setupable_id' => $wager->id,
                        'created_at' => $validated['attendance_date']
                    ]);

                    $attendance = Attendance::create([
                        'attendance_date' => $validated['attendance_date'],
                        'attendance_setup_id' => $setup->id,
                        'is_present' => 1,
                        'created_at' => $validated['attendance_date']

                    ]);

                    $records[] = $attendance;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance records saved successfully.',
                'data' => $records,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if ($request->ajax()) {

            try {
                $validator = Validator::make($request->all(), [
                    'attendance_id' => 'required|exists:attendance_setups,id',
                    'created_at' => 'required|date',
                    'site_id' => 'required|exists:sites,id',
                    'is_present' => 'sometimes|boolean',
                ], [
                    'attendance_id.exists' => 'The selected attendance id not exist.',
                    'created_at.required' => 'Attendance date is required.',
                    'site_id.exists' => 'The selected site is invalid.',
                ]);


                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $attendnace = Attendance::findOrFail($request->attendance_id);


                $attendnace->update([
                    'attendance_date' => $request->created_at,
                    'created_at' => $request->created_at,
                    'site_id' => $request->site_id,
                    'is_present' => $request->is_present ? 1 : 0,
                ]);


                return response()->json([
                    'status' => true,
                    'message' => 'Attendance setup updated successfully',
                ], 200);

            } catch (Exception $e) {

                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong while updating.',
                ], 500);

            }



        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

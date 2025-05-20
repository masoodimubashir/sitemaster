<?php

namespace App\Http\Controllers;

use App\Models\Labour;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Wasta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceSheetController extends Controller
{


    public function index(Request $request)
    {

        if ($request->filled('monthYear')) {
            [$year, $month] = explode('-', $request->input('monthYear'));
        } else {
            $month = now()->month;
            $year = now()->year;
        }

        $wastas = Wasta::with([
            'attendances' => fn($query) => $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year),
            'labours.attendances' => fn($query) => $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year),
        ])->get();

        $phases = Phase::latest()->get();

        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', compact('wastas', 'month', 'year', 'daysInMonth', 'phases'));
    }

    public function storeWastaAttendance(Request $request)
    {

        try {

            $data = Validator::make($request->only('wasta_id',  'is_present', 'date'), [
                'wasta_id' => 'required|exists:wastas,id',
                'is_present' => 'required|boolean',
                'date' => 'required|date'
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            $wasta = Wasta::find($data->validated()['wasta_id']);

            $wasta->attendances()->create([
                'attendable_type' => 'App\Models\Wasta',
                'attendable_id' => $wasta->id,
                'is_present' => $data->validated()['is_present'],
                'attendance_date' =>  now(),
            ]);

            return response()->json([
                'message' => 'Wasta created successfully',
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function storelabourAttendance(Request $request)
    {

        try {

            $data = Validator::make($request->only('labour_id',  'is_present', 'date'), [
                'labour_id' => 'required|exists:labours,id',
                'is_present' => 'required|boolean',
                'date' => 'required|date'
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            $labour = Labour::find($data->validated()['labour_id']);

            $labour->attendances()->create([
                'attendable_type' => 'App\Models\Wasta',
                'attendable_id' => $labour->id,
                'is_present' => $data->validated()['is_present'],
                'attendance_date' =>  now(),
            ]);

            return response()->json([
                'message' => 'Wasta created successfully',
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function storeLabour(Request $request)
    {

        try {

            $data = Validator::make($request->only('wasta_id', 'labour_name', 'price', 'contact', 'phase_id'), [
                "wasta_id" => "required|exists:wastas,id",
                "phase_id" => "required|exists:phases,id",
                "labour_name" => "required|string|max:255",
                "price" => "required|numeric",
                "contact" => "required|string|max:10",
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            Labour::create([
                'wasta_id' => $data->validated()['wasta_id'],
                'labour_name' => $data->validated()['labour_name'],
                'price' => $data->validated()['price'],
                'contact_no' => $data->validated()['contact'],
                'phase_id' => $data->validated()['phase_id'],
            ]);

            return response()->json([
                'message' => 'Labour Created Successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function updateLabour(Request $request, $id)
    {

        if ($request->ajax()) {

            try {


                $data = Validator::make($request->only('labour_name'), [
                    "labour_name" => "required|string|max:255",
                ]);

                if ($data->fails()) {
                    return response()->json([
                        'errors' => $data->errors()
                    ], 422);
                }

                Labour::where('id', $id)->update([
                    'labour_name' => $data->validated()['labour_name'],
                ]);

                return response()->json([
                    'message' => 'Labour Updated Successfully',
                ], 200);
            } catch (Exception $e) {
                Log::error($e->getMessage());

                return response()->json([
                    'message' => 'Something went wrong',
                ], 500);
            }
        }
    }


    public function updateWasta(Request $request, $id)
    {


        try {

            $data = Validator::make($request->only('wasta_name'), [
                "wasta_name" => "required|string|max:255",
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            Wasta::where('id', $id)->update([
                'wasta_name' => $data->validated()['wasta_name'],
            ]);

            return response()->json([
                'message' => 'Wasta Updated Successfully',
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function showAttendanceBySite(Request $request, string $id)
    {

        $site = Site::find($id);

        if ($request->filled('monthYear')) {
            [$year, $month] = explode('-', $request->input('monthYear'));
        } else {
            $month = now()->month;
            $year = now()->year;
        }

        $wastas = Wasta::with([
            'attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            },
            'labours.attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            }
        ])
            ->whereHas('phase.site', function ($query) use ($site) {
                $query->where('id', $site->id);
            })
            ->get();

        $sites = Site::where('is_on_going', 1)->get();

        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

        return view('profile.partials.Admin.Ledgers.site-wager-attendance-sheet', compact('wastas', 'month', 'year', 'daysInMonth', 'sites', 'site'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyWager;
use App\Models\Labour;
use App\Models\Site;
use App\Models\WagerAttendance;
use App\Models\Wasta;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

        $wastas = Wasta::with(['attendances' => function ($query) use ($month, $year): void {
            $query->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);
        }])->get();

        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;

        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', compact('wastas', 'month', 'year', 'daysInMonth'));
    }

    public function updateWastaAttendance(Request $request)
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
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }


    public function storeLabour(Request $request)
    {

        try {


            $data = Validator::make($request->only('wasta_id', 'labour_name', 'price', 'contact'), [
                "wasta_id" => "required|exists:wastas,id",
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
            ]);

            return response()->json([
                'message' => 'Labour Created Successfully',
            ], 200);

        } catch (\Exception $e) {

            log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }

    }
}

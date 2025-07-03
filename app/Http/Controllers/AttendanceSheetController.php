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

        // Load Wastas with Attendance + Labours
        $wastasQuery = Wasta::with([
            'attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            },
            'labours.attendances' => function ($query) use ($month, $year) {
                $query->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year);
            },
            'phase.site'
        ]);

        // Filter by site if selected
        if ($request->filled('site_id')) {
            $wastasQuery->whereHas('phase.site', function ($query) use ($request) {
                $query->where('id', $request->input('site_id'));
            });
        }

        // Final fetch and calculation
        $wastas = $wastasQuery->get()->map(function ($wasta) {
            $wasta->present_days = $wasta->attendances->where('is_present', true)->count();
            $wasta->total_amount = $wasta->present_days * $wasta->price;

            $wasta->labours->each(function ($labour) {
                $labour->present_days = $labour->attendances->where('is_present', true)->count();
                $labour->total_amount = $labour->present_days * $labour->price;
            });

            $wasta->labours_total_amount = $wasta->labours->sum('total_amount');
            $wasta->combined_total = $wasta->total_amount + $wasta->labours_total_amount;

            return $wasta;
        });

        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $phases = Phase::with('site')->latest()->get();
        $sites = Site::where('is_on_going', 1)->get();

         // Calculate site totals
        $siteTotal = [
            'wasta_amount' => $wastas->sum('total_amount'),
            'labour_amount' => $wastas->sum('labours_total_amount'),
            'combined_total' => $wastas->sum('combined_total')
        ];

        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', [
            'wastas' => $wastas,
            'month' => $month,
            'year' => $year,
            'daysInMonth' => $daysInMonth,
            'phases' => $phases,
            'sites' => $sites,
            'selectedSiteId' => $request->input('site_id'),
            'siteTotal' => $siteTotal
        ]);
    }


    public function storeWastaAttendance(Request $request)
    {

        try {

            $data = Validator::make($request->only('wasta_id', 'is_present', 'date'), [
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
                'attendance_date' => now(),
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

            $data = Validator::make($request->only('labour_id', 'is_present', 'date'), [
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
                'attendance_date' => now(),
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
            },
            'phase'
        ])
            ->whereHas('phase.site', function ($query) use ($site) {
                $query->where('id', $site->id);
            })
            ->get()
            ->map(function ($wasta) use ($month, $year) {
                // Calculate present days for wasta
                $wasta->present_days = $wasta->attendances->where('is_present', true)->count();
                $wasta->total_amount = $wasta->present_days * $wasta->price;

                // Calculate for each labour
                $wasta->labours->each(function ($labour) use ($month, $year) {
                    $labour->present_days = $labour->attendances->where('is_present', true)->count();
                    $labour->total_amount = $labour->present_days * $labour->price;
                });

                $wasta->labours_total_amount = $wasta->labours->sum('total_amount');
                $wasta->combined_total = $wasta->total_amount + $wasta->labours_total_amount;

                return $wasta;
            });



        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $phases = Phase::where('site_id', $site->id)->orderBy('phase_name')->get();



        // Calculate site totals
        $siteTotal = [
            'wasta_amount' => $wastas->sum('total_amount'),
            'labour_amount' => $wastas->sum('labours_total_amount'),
            'combined_total' => $wastas->sum('combined_total')
        ];


        return view('profile.partials.Admin.Ledgers.site-wager-attendance-sheet', compact(
            'wastas',
            'month',
            'year',
            'daysInMonth',
            'site',
            'phases',
            'siteTotal'
        ));
    }
}

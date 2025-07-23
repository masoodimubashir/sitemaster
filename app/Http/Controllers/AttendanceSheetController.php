<?php

namespace App\Http\Controllers;

use App\Models\Labour;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Wasta;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AttendanceSheetController extends Controller
{



    public function index(Request $request)
    {
        // Handle date filtering
        $dateFilter = $request->input('date_filter', 'month');
        $monthYear = $request->input('monthYear', now()->format('Y-m'));
        $customStart = $request->input('custom_start', now()->startOfMonth()->format('Y-m-d'));
        $customEnd = $request->input('custom_end', now()->endOfMonth()->format('Y-m-d'));

        // Set date range based on filter type
        if ($dateFilter === 'month') {
            [$year, $month] = explode('-', $monthYear);
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDays = $startDate->daysInMonth;
        } else {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
            $totalDays = $startDate->diffInDays($endDate) + 1; // Inclusive of both start and end dates
        }

        // Base query for wastas with eager loading
        $wastasQuery = Wasta::with([
            'attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('attendance_date', [$startDate, $endDate]);
            },
            'labours.attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('attendance_date', [$startDate, $endDate]);
            },
            'phase.site'
        ]);

        // Filter by site if selected
        if ($request->filled('site_id')) {
            $wastasQuery->whereHas('phase.site', function ($query) use ($request) {
                $query->where('id', $request->input('site_id'));
            });
        }

        // Filter by phase if selected
        if ($request->filled('phase_id')) {
            $wastasQuery->where('phase_id', $request->phase_id);
        }

        // Paginate and transform results
        $perPage = 10;
        $wastas = $wastasQuery->paginate($perPage)->through(function ($wasta) {
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

        // Get all sites and phases for filters
        $sites = Site::where('is_on_going', true)->get();
        $phases = Phase::with('site')->latest()->get();

        // Calculate totals
        $siteTotal = [
            'wasta_amount' => $wastas->sum('total_amount'),
            'labour_amount' => $wastas->sum('labours_total_amount'),
            'combined_total' => $wastas->sum('combined_total')
        ];

        $totalLabours = $wastas->sum(fn($w) => $w->labours->count());


        // Format date range for display
        $dateRange = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', [
            'wastas' => $wastas,
            'sites' => $sites,
            'phases' => $phases,
            'siteTotal' => $siteTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalDays' => $totalDays,
            'dateRange' => $dateRange,
            'dateFilter' => $dateFilter,
            'monthYear' => $monthYear,
            'customStart' => $customStart,
            'customEnd' => $customEnd,
            'selectedSiteId' => $request->input('site_id'),
            'selectedPhaseId' => $request->input('phase_id'),
            'totalLabours' => $totalLabours,
        ]);
    }



    public function storeWastaAttendance(Request $request)
    {



        try {
            $data = Validator::make($request->only('wasta_id', 'is_present', 'attendance_id', 'attendance_date'), [
                'wasta_id' => 'required|exists:wastas,id',
                'is_present' => 'required|boolean',
                'attendance_id' => 'nullable|exists:attendances,id',
                'attendance_date' => 'required|date',
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            $validatedData = $data->validated();
            $wasta = Wasta::find($validatedData['wasta_id']);

            $wasta->attendances()->updateOrCreate(
                [
                    'attendable_type' => 'App\Models\Wasta',
                    'attendable_id' => $wasta->id,
                    'id' => $validatedData['attendance_id'],
                    'attendance_date' => $validatedData['attendance_date'],
                ],
                [
                    'is_present' => $validatedData['is_present'],
                ]
            );

            return response()->json([
                'message' => 'Wasta attendance updated successfully',
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }

    }

    public function storelabourAttendance(Request $request)
    {
        try {

            $data = Validator::make($request->only('labour_id', 'is_present', 'attendance_id', 'attendance_date'), [
                'labour_id' => 'required|exists:labours,id',
                'is_present' => 'required|boolean',
                'attendance_id' => 'nullable|exists:attendances,id',
                'attendance_date' => 'required|date',
            ]);

            if ($data->fails()) {
                return response()->json([
                    'errors' => $data->errors()
                ], 422);
            }

            $validatedData = $data->validated();
            $labour = Labour::find($validatedData['labour_id']);

            $labour->attendances()->updateOrCreate(
                [
                    'attendable_type' => 'App\Models\Labour',
                    'attendable_id' => $labour->id,
                    'id' => $validatedData['attendance_id'],
                    'attendance_date' => $validatedData['attendance_date'],
                ],
                [
                    'is_present' => $validatedData['is_present'],
                ]
            );

            return response()->json([
                'message' => 'Labour attendance updated successfully',
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
        $site = Site::findOrFail($id);

        // Handle date filtering
        $dateFilter = $request->input('date_filter', 'month');
        $monthYear = $request->input('monthYear', now()->format('Y-m'));
        $customStart = $request->input('custom_start', now()->startOfMonth()->format('Y-m-d'));
        $customEnd = $request->input('custom_end', now()->endOfMonth()->format('Y-m-d'));

        // Set date range based on filter type
        if ($dateFilter === 'month') {
            [$year, $month] = explode('-', $monthYear);
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = $startDate->copy()->endOfMonth();
            $totalDays = $startDate->daysInMonth;
        } else {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
            $totalDays = round($startDate->diffInDays($endDate)); // Fixed calculation
        }

        // Base query for wastas
        $wastasQuery = Wasta::with([
            'attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('attendance_date', [$startDate, $endDate]);
            },
            'labours.attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('attendance_date', [$startDate, $endDate]);
            },
            'phase'
        ])->whereHas('phase.site', function ($query) use ($site) {
            $query->where('id', $site->id);
        });

        // Filter by phase if selected
        if ($request->filled('phase_id')) {
            $wastasQuery->where('phase_id', $request->phase_id);
        }

        // Paginate and transform results
        $perPage = 10;
        $wastas = $wastasQuery->paginate($perPage)->through(function ($wasta) {
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

        $phases = Phase::where('site_id', $site->id)->orderBy('phase_name')->get();

        $totalLabours = $wastas->sum(fn($w) => $w->labours->count());

        // Calculate site totals
        $siteTotal = [
            'wasta_amount' => $wastas->sum('total_amount'),
            'labour_amount' => $wastas->sum('labours_total_amount'),
            'combined_total' => $wastas->sum('combined_total')
        ];

        // Format date range for display
        $dateRange = $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y');

        return view('profile.partials.Admin.Ledgers.site-wager-attendance-sheet', compact(
            'wastas',
            'site',
            'phases',
            'siteTotal',
            'startDate',
            'endDate',
            'totalDays',
            'dateRange',
            'dateFilter',
            'monthYear',
            'customStart',
            'customEnd',
            'totalLabours'
        ));
    }




}

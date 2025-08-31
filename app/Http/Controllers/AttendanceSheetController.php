<?php

namespace App\Http\Controllers;

use App\Models\Labour;
use App\Models\Site;
use App\Models\Wager;
use App\Models\Wasta;
use App\Models\AttendanceSetup;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AttendanceSheetController extends Controller
{


    // TODO : The Index Method Has Yet To Be Implemented

    public function index(Request $request)
    {

        // Handle month filter - if month_filter is provided, override start_date and end_date
        if ($request->filled('month_filter')) {
            $monthYear = explode('-', $request->month_filter);
            $year = $monthYear[0];
            $month = $monthYear[1];

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } else {
            // Use provided dates or default to current month
            $startDate = Carbon::parse($request->start_date ?? now()->startOfMonth());
            $endDate = Carbon::parse($request->end_date ?? now()->endOfMonth());
        }

        // Ensure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }

        // Get all dates in the range
        $dates = CarbonPeriod::create($startDate, $endDate);
        $dateArray = iterator_to_array($dates);
        $totalDays = count($dateArray);

        // Base query for wastas with eager loading
        $wastaQuery = Wasta::with([
            'attendanceSetups.attendances' => fn($q) => $q->whereBetween('attendance_date', [$startDate, $endDate]),
            'wagers.attendanceSetups.attendances' => fn($q) => $q->whereBetween('attendance_date', [$startDate, $endDate]),
        ]);

        // Apply wasta filter if selected
        if ($request->filled('wasta_id')) {
            $wastaQuery->where('id', $request->wasta_id);
        }

        $wastas = $wastaQuery->get();

        // Base query for independent workers
        $independentQuery = Wager::whereNull('wasta_id')
            ->with(['attendanceSetups.attendances' => fn($q) => $q->whereBetween('attendance_date', [$startDate, $endDate])]);

        // Apply wager filter if selected (for independent workers)
        if ($request->filled('wager_id')) {
            $independentQuery->where('id', $request->wager_id);
        }

        $independents = $independentQuery->get();

        // Process data for display
        $attendanceData = [];
        $grandTotalDays = 0;
        $grandTotalAmount = 0;

        // Process Wastas and their workers
        foreach ($wastas as $wasta) {
            // Skip if we're filtering by worker type and it's not contractors
            if ($request->worker_type === 'workers' || $request->worker_type === 'independents') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $wasta->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $wasta->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'wasta_' . $wasta->id,
                'name' => $wasta->wasta_name,
                'type' => 'Contractor',
                'rate' => $wasta->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => true,
                'parent_id' => null
            ];

            // Process wagers under this wasta
            foreach ($wasta->wagers as $wager) {
                // Skip if we're filtering by worker type and it's not workers
                if ($request->worker_type === 'contractors' || $request->worker_type === 'independents') {
                    continue;
                }

                // Apply wager filter if selected
                if ($request->filled('wager_id') && $wager->id != $request->wager_id) {
                    continue;
                }

                $dailyAttendance = [];
                $presentCount = 0;

                foreach ($dateArray as $date) {
                    $present = $wager->attendanceSetups->flatMap->attendances
                        ->firstWhere('attendance_date', $date->format('Y-m-d'));
                    $isPresent = $present && $present->is_present;

                    // Skip if filtering by attendance status
                    if ($request->attendance_status === 'present' && !$isPresent)
                        continue;
                    if ($request->attendance_status === 'absent' && $isPresent)
                        continue;

                    $dailyAttendance[] = $isPresent;
                    if ($isPresent)
                        $presentCount++;
                }

                // Skip if no attendance matches the filter
                if ($request->attendance_status === 'present' && $presentCount === 0)
                    continue;
                if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                    continue;

                $amount = $wager->price * $presentCount;
                $grandTotalDays += $presentCount;
                $grandTotalAmount += $amount;

                $attendanceData[] = [
                    'id' => 'wager_' . $wager->id,
                    'name' => $wager->wager_name,
                    'type' => 'Worker',
                    'rate' => $wager->price,
                    'daily' => $dailyAttendance,
                    'days' => $presentCount,
                    'amount' => $amount,
                    'is_contractor' => false,
                    'parent_id' => 'wasta_' . $wasta->id
                ];
            }
        }

        // Process independent workers
        foreach ($independents as $worker) {
            // Skip if we're filtering by worker type and it's not independents
            if ($request->worker_type === 'contractors' || $request->worker_type === 'workers') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $worker->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $worker->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'independent_' . $worker->id,
                'name' => $worker->wager_name,
                'type' => 'Independent',
                'rate' => $worker->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => false,
                'parent_id' => null
            ];
        }

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $attendanceData = array_filter($attendanceData, function ($item) use ($searchTerm) {
                return str_contains(strtolower($item['name']), $searchTerm);
            });
        }

        // Calculate totals for statistics
        $totalWorkers = count(array_filter($attendanceData, fn($item) => !$item['is_contractor']));
        $totalContractors = count(array_filter($attendanceData, fn($item) => $item['is_contractor']));

        // Paginate results
        $perPage = $request->per_page ?? 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($attendanceData, ($currentPage - 1) * $perPage, $perPage);

        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            count($attendanceData),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        // Get filter options for dropdowns
        $wastas = Wasta::select('wasta_name', 'id')->latest()->get();
        $wagers = Wager::select('wager_name', 'id')->latest()->get();


        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', [
            'site',
            'startDate',
            'endDate',
            'dateArray',
            'totalDays',
            'paginatedData',
            'grandTotalDays',
            'grandTotalAmount',
            'totalWorkers',
            'totalContractors',
            'wastas',
            'wagers'
        ]);


    }

    public function storeWastaAttendance(Request $request)
    {

        try {

//            dd($request->all());

            $validator = Validator::make($request->only('wasta_name','wasta_id', 'site_id', 'is_present', 'attendance_date', 'daily_price'), [
                'wasta_id' => 'required|exists:wastas,id',
                'site_id' => 'required|exists:sites,id',
                'is_present' => 'required|boolean',
                'attendance_date' => 'required|date',
                'daily_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            DB::beginTransaction();

            $wasta = Wasta::findOrFail($data['wasta_id']);

            // Keep the rate column consistent with the edited daily price
            if (isset($data['daily_price'])) {
                $wasta->update(['price' => $data['daily_price']]);
            }

            // Find or create the attendance setup for this site and wasta
            $setup = AttendanceSetup::firstOrCreate(
                [
                    'site_id' => $data['site_id'],
                    'setupable_id' => $wasta->id,
                    'setupable_type' => Wasta::class,
                ],
                [
                    'name' => $wasta->wasta_name,
                    'count' => 1,
                    'price' => $data['daily_price'],
                ]
            );

            // Always keep setup price in sync with the provided daily price
            $setup->update([
                'name' => $wasta->wasta_name,
                'price' => $data['daily_price'],
            ]);

            // Upsert attendance for the given date
            Attendance::updateOrCreate(
                [
                    'attendance_setup_id' => $setup->id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'is_present' => (bool) $data['is_present'],
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Wasta attendance updated successfully',
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }

    }

    public function storelabourAttendance(Request $request)
    {
        try {

            $validator = Validator::make($request->only('labour_id', 'site_id', 'is_present', 'attendance_date', 'daily_price'), [
                'labour_id' => 'required|exists:wagers,id',
                'site_id' => 'required|exists:sites,id',
                'is_present' => 'required|boolean',
                'attendance_date' => 'required|date',
                'daily_price' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();

            DB::beginTransaction();

            $wager = Wager::findOrFail($data['labour_id']);

            // Keep the rate column consistent with the edited daily price
            if (isset($data['daily_price'])) {
                $wager->update(['price' => $data['daily_price']]);
            }

            // Find or create the attendance setup for this site and worker (wager)
            $setup = AttendanceSetup::firstOrCreate(
                [
                    'site_id' => $data['site_id'],
                    'setupable_id' => $wager->id,
                    'setupable_type' => Wager::class,
                ],
                [
                    'name' => $wager->wager_name,
                    'count' => 1,
                    'price' => $data['daily_price'],
                ]
            );

            // Always keep setup price in sync with the provided daily price
            $setup->update([
                'name' => $wager->wager_name,
                'price' => $data['daily_price'],
            ]);

            // Upsert attendance for the given date
            Attendance::updateOrCreate(
                [
                    'attendance_setup_id' => $setup->id,
                    'attendance_date' => $data['attendance_date'],
                ],
                [
                    'is_present' => (bool) $data['is_present'],
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Labour attendance updated successfully',
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function storeLabour(Request $request)
    {

        try {

            $data = Validator::make($request->only('wasta_id', 'labour_name', 'contact', 'phase_id'), [
                "wasta_id" => "required|exists:wastas,id",
                "phase_id" => "required|exists:phases,id",
                "labour_name" => "required|string|max:255",
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

                // Note: In the attendance table, "labour" refers to a worker row which maps to Wager model
                Wager::where('id', $id)->update([
                    'wager_name' => $data->validated()['labour_name'],
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

//            dd($request->all());

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

            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Something went wrong',
            ], 500);
        }
    }

    public function showAttendanceBySite(Request $request, $id)
    {


        $site = Site::findOrFail(base64_decode($id));

        // Handle month filter - if month_filter is provided, override start_date and end_date
        if ($request->filled('month_filter')) {
            $monthYear = explode('-', $request->month_filter);
            $year = $monthYear[0];
            $month = $monthYear[1];

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        } else {
            // Use provided dates or default to current month
            $startDate = Carbon::parse($request->start_date ?? now()->startOfMonth());
            $endDate = Carbon::parse($request->end_date ?? now()->endOfMonth());
        }

        // Ensure end date is not before start date
        if ($endDate->lt($startDate)) {
            $endDate = $startDate->copy();
        }

        // Get all dates in the range
        $dates = CarbonPeriod::create($startDate, $endDate);
        $dateArray = iterator_to_array($dates);
        $totalDays = count($dateArray);

        // Base query for wastas with eager loading scoped to site
        $wastaQuery = Wasta::whereHas('attendanceSetups', function ($q) use ($site) {
            $q->where('site_id', $site->id);
        })
            ->with([
                'attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
                'wagers.attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
            ]);

        // Apply wasta filter if selected
        if ($request->filled('wasta_id')) {
            $wastaQuery->where('id', $request->wasta_id);
        }

        $wastas = $wastaQuery->get();

        // Base query for independent workers (no wasta) scoped to site
        $independentQuery = Wager::whereNull('wasta_id')
            ->whereHas('attendanceSetups', function ($q) use ($site) {
                $q->where('site_id', $site->id);
            })
            ->with([
                'attendanceSetups' => fn($q) => $q->where('site_id', $site->id)
                    ->with(['attendances' => fn($a) => $a->whereBetween('attendance_date', [$startDate, $endDate])]),
            ]);

        // Apply wager filter if selected (for independent workers)
        if ($request->filled('wager_id')) {
            $independentQuery->where('id', $request->wager_id);
        }

        $independents = $independentQuery->get();

        // Process data for display
        $attendanceData = [];
        $grandTotalDays = 0;
        $grandTotalAmount = 0;

        // Process Wastas and their workers
        foreach ($wastas as $wasta) {
            // Skip if we're filtering by worker type and it's not contractors
            if ($request->worker_type === 'workers' || $request->worker_type === 'independents') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $wasta->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $wasta->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'wasta_' . $wasta->id,
                'name' => $wasta->wasta_name,
                'type' => 'Contractor',
                'rate' => $wasta->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => true,
                'parent_id' => null
            ];

            // Process wagers under this wasta
            foreach ($wasta->wagers as $wager) {
                // Skip if we're filtering by worker type and it's not workers
                if ($request->worker_type === 'contractors' || $request->worker_type === 'independents') {
                    continue;
                }

                // Apply wager filter if selected
                if ($request->filled('wager_id') && $wager->id != $request->wager_id) {
                    continue;
                }

                $dailyAttendance = [];
                $presentCount = 0;

                foreach ($dateArray as $date) {
                    $present = $wager->attendanceSetups->flatMap->attendances
                        ->firstWhere('attendance_date', $date->format('Y-m-d'));
                    $isPresent = $present && $present->is_present;

                    // Skip if filtering by attendance status
                    if ($request->attendance_status === 'present' && !$isPresent)
                        continue;
                    if ($request->attendance_status === 'absent' && $isPresent)
                        continue;

                    $dailyAttendance[] = $isPresent;
                    if ($isPresent)
                        $presentCount++;
                }

                // Skip if no attendance matches the filter
                if ($request->attendance_status === 'present' && $presentCount === 0)
                    continue;
                if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                    continue;

                $amount = $wager->price * $presentCount;
                $grandTotalDays += $presentCount;
                $grandTotalAmount += $amount;

                $attendanceData[] = [
                    'id' => 'wager_' . $wager->id,
                    'name' => $wager->wager_name,
                    'type' => 'Worker',
                    'rate' => $wager->price,
                    'daily' => $dailyAttendance,
                    'days' => $presentCount,
                    'amount' => $amount,
                    'is_contractor' => false,
                    'parent_id' => 'wasta_' . $wasta->id
                ];
            }
        }

        // Process independent workers
        foreach ($independents as $worker) {
            // Skip if we're filtering by worker type and it's not independents
            if ($request->worker_type === 'contractors' || $request->worker_type === 'workers') {
                continue;
            }

            $dailyAttendance = [];
            $presentCount = 0;

            foreach ($dateArray as $date) {
                $present = $worker->attendanceSetups->flatMap->attendances
                    ->firstWhere('attendance_date', $date->format('Y-m-d'));
                $isPresent = $present && $present->is_present;

                // Skip if filtering by attendance status
                if ($request->attendance_status === 'present' && !$isPresent)
                    continue;
                if ($request->attendance_status === 'absent' && $isPresent)
                    continue;

                $dailyAttendance[] = $isPresent;
                if ($isPresent)
                    $presentCount++;
            }

            // Skip if no attendance matches the filter
            if ($request->attendance_status === 'present' && $presentCount === 0)
                continue;
            if ($request->attendance_status === 'absent' && $presentCount === count($dateArray))
                continue;

            $amount = $worker->price * $presentCount;
            $grandTotalDays += $presentCount;
            $grandTotalAmount += $amount;

            $attendanceData[] = [
                'id' => 'independent_' . $worker->id,
                'name' => $worker->wager_name,
                'type' => 'Independent',
                'rate' => $worker->price,
                'daily' => $dailyAttendance,
                'days' => $presentCount,
                'amount' => $amount,
                'is_contractor' => false,
                'parent_id' => null
            ];
        }

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->search);
            $attendanceData = array_filter($attendanceData, function ($item) use ($searchTerm) {
                return str_contains(strtolower($item['name']), $searchTerm);
            });
        }

        // Calculate totals for statistics
        $totalWorkers = count(array_filter($attendanceData, fn($item) => !$item['is_contractor']));
        $totalContractors = count(array_filter($attendanceData, fn($item) => $item['is_contractor']));

        // Paginate results
        $perPage = $request->per_page ?? 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($attendanceData, ($currentPage - 1) * $perPage, $perPage);

        $paginatedData = new LengthAwarePaginator(
            $currentItems,
            count($attendanceData),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        // Get filter options for dropdowns
        $wastas = Wasta::select('wasta_name', 'id')->latest()->get();
        $wagers = Wager::select('wager_name', 'id')->latest()->get();

        return view('profile.partials.admin.ledgers.site-wager-attendance-sheet', compact(
            'site',
            'startDate',
            'endDate',
            'dateArray',
            'totalDays',
            'paginatedData',
            'grandTotalDays',
            'grandTotalAmount',
            'totalWorkers',
            'totalContractors',
            'wastas',
            'wagers'
        ));

    }


}

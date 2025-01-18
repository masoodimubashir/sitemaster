<?php

namespace App\Http\Controllers;

use App\Models\DailyWager;
use App\Models\Site;
use App\Models\WagerAttendance;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSheetController extends Controller
{
    public function index(Request $request)
    {

        $perPage = $request->get('per_page', 20);
        $currentPage = $request->input('page', 1);


        // Retrieve filter parameters
        $siteId = request('site_id', 'all');
        $wagerId = request('wager_id', 'all');
        $dateFilter = request('date_filter', 'today');


        Log::debug('data', [$siteId, $wagerId, $dateFilter]);

        // Start the base query
        $wagers = DailyWager::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    },
                    'wagerAttendances' => function ($attendance) {
                        $attendance->where('verified_by_admin', 1);
                    }
                ])->withoutTrashed();
            },
            'supplier' => function ($supplier) {
                $supplier->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phaseQuery) {
                $phaseQuery->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplierQuery) {
                $supplierQuery->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($siteQuery) {
                $siteQuery->whereNull('deleted_at');
            });

        // Apply filters based on form input
        if ($siteId !== 'all') {
            $wagers->whereHas('phase.site', function ($query) use ($siteId) {
                $query->where('site_id', $siteId);
            });
        }

        if ($wagerId !== 'all') {
             $wagers->where('id', $wagerId);
        }

        switch ($dateFilter) {
            case 'today':
                $wagers->whereDate('created_at', today());
                break;
            case 'yesterday':
                $wagers->whereDate('created_at', today()->subDay());
                break;
            case 'this_week':
                $wagers->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'this_month':
                $wagers->whereMonth('created_at', now()->month);
                break;
            case 'this_year':
                $wagers->whereYear('created_at', now()->year);
                break;
            case 'lifetime':
                break;
        }

        // Fetch wagers and process
        $wagers = $wagers->get();

        $data = $wagers
            ->filter(function ($wager) {
                return $wager->wagerAttendances->where('verified_by_admin', 1)->isNotEmpty();
            })
            ->map(function ($wager) {

                $noOfPersons = $wager->wagerAttendances
                    ->where('verified_by_admin', 1)
                    ->sum('no_of_persons');

                return [
                    'id' => $wager->id,
                    'wager_name' => $wager->wager_name,
                    'no_of_persons' => $noOfPersons,
                    'supplier' => $wager->supplier->name,
                    'phase' => $wager->phase->phase_name,
                    'site' => $wager->phase->site->site_name,
                    'site_id' => $wager->phase->site->id,
                    'supplier_id' => $wager->supplier->id,
                    'created_at' => $wager->created_at->format('D-m-y : h-s'),
                ];
            });

        // Paginate the results
        $paginatedLedgers = new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage),
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $wagers = $paginatedLedgers->unique('id');

        $sites = $paginatedLedgers->unique('site_id');

        return view('profile.partials.Admin.Ledgers.wager-attendance-sheet', compact('paginatedLedgers', 'wagers', 'sites'));
    }
}

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

        $siteId = request('site_id', 'all');
        $wagerId = request('wager_id', 'all');
        $dateFilter = request('date_filter', 'today');

        $wagers = DailyWager::query()
            ->with(['phase.site', 'phase.wagerAttendances', 'supplier'])
            ->whereHas('phase', fn($phase) => $phase->withoutTrashed())
            ->whereHas('supplier', fn($supplier) => $supplier->withoutTrashed())
            ->whereHas('phase.site', fn($site) => $site->withoutTrashed())
            ->when($siteId !== 'all', fn($query) => $query->where('site_id', $siteId))
            ->when($wagerId !== 'all', fn($query) => $query->where('id', $wagerId))
            ->when($dateFilter, function ($query) use ($dateFilter) {
                return match ($dateFilter) {
                    'today' => $query->whereDate('created_at', today()),
                    'yesterday' => $query->whereDate('created_at', today()->subDay()),
                    'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                    'this_month' => $query->whereMonth('created_at', now()->month),
                    'this_year' => $query->whereYear('created_at', now()->year),
                    default => $query
                };
            })
            ->get();

        $data = $wagers
            ->filter(fn($wager) => $wager->wagerAttendances->where('verified_by_admin', 1)->isNotEmpty())
            ->map(fn($wager) => [
                'id' => $wager->id,
                'wager_name' => $wager->wager_name,
                'no_of_persons' => $wager->wagerAttendances->where('verified_by_admin', 1)->sum('no_of_persons'),
                'supplier' => $wager->supplier->name,
                'phase' => $wager->phase->phase_name,
                'site' => $wager->phase->site->site_name,
                'site_id' => $wager->phase->site->id,
                'supplier_id' => $wager->supplier->id,
                'created_at' => $wager->created_at->format('D-m-y : h-s'),
            ]);

        $paginatedLedgers = new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage),
            $data->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $wagers = $paginatedLedgers->unique('id');
        $sites = $paginatedLedgers->unique('site_id');

        return view(
            'profile.partials.Admin.Ledgers.wager-attendance-sheet',
            compact('paginatedLedgers', 'wagers', 'sites')
        );
    }
}

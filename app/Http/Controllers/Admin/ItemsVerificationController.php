<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\Labour;
use App\Models\Phase;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use App\Models\WagerAttendance;
use App\Models\Wasta;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ItemsVerificationController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->input('site_id');
        $phaseName = $request->input('phase');
        $supplierName = $request->input('supplier');
        $verificationStatus = $request->input('verification_status');
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        // Fetch items
        $attendanceItems = $this->getAttendanceItems($siteId, $phaseName, $verificationStatus, $from_date, $to_date);
        $otherItems = $this->getOtherItems($siteId, $phaseName, $supplierName, $verificationStatus, $from_date, $to_date);

        $data = $otherItems->merge($attendanceItems)->sortByDesc('created_at');

        $filterOptions = $this->getFilterOptions();

        // Pagination
        $perPage = $request->get('per_page', 10);
        $paginatedData = new LengthAwarePaginator(
            $data->forPage($request->input('page', 1), $perPage),
            $data->count(),
            $perPage,
            $request->input('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view("profile.partials.Admin.Site.show_unverified_items", array_merge(
            ['paginatedData' => $paginatedData],
            $filterOptions
        ));
    }


    protected function getAttendanceItems($siteId, $phaseName, $verificationStatus, $from_date = null, $to_date = null)
    {
        $query = Attendance::with([
            'attendable' => function ($q) {
                $q->with(['phase.site']);

                if ($q->getModel() instanceof Labour) {
                    $q->with('wasta');
                }
            }
        ])
            ->whereIn('attendable_type', [Wasta::class, Labour::class])
            ->whereHas('attendable', function ($q) use ($siteId, $phaseName) {
                $q->whereHas('phase.site', function ($q) use ($siteId) {
                    $q->whereNull('deleted_at');
                    if ($siteId && $siteId !== 'all') {
                        $q->where('id', $siteId);
                    }
                })->whereHas('phase', function ($q) use ($phaseName) {
                    $q->whereNull('deleted_at');
                    if ($phaseName && $phaseName !== 'all') {
                        $q->where('phase_name', $phaseName);
                    }
                });
            });

        // Apply verification status
        if ($verificationStatus && $verificationStatus !== 'all') {
            $query->where('is_present', $verificationStatus === 'verified');
        }

        // Apply date filters
        $query->when($from_date, fn($q) => $q->whereDate('created_at', '>=', $from_date))
            ->when($to_date, fn($q) => $q->whereDate('created_at', '<=', $to_date));

        return $query->latest()
            ->get()
            ->map(function ($attendance) {
                $attendable = $attendance->attendable;
                if (!$attendable)
                    return null;

                $isWasta = $attendable instanceof Wasta;
                $isLabour = $attendable instanceof Labour;

                $supplierName = $isLabour
                    ? ($attendable->relationLoaded('wasta') ? $attendable->wasta->wasta_name : 'NA')
                    : 'NA';

                $supplierId = $isLabour ? $attendable->wasta_id : null;

                return [
                    'id' => $attendance->id,
                    'phase' => $attendable->phase->phase_name ?? 'NA',
                    'description' => $isWasta ? $attendable->wasta_name : $attendable->labour_name,
                    'site' => $attendable->phase->site->site_name ?? 'NA',
                    'category' => 'Attendance',
                    'site_id' => $attendable->phase->site_id ?? null,
                    'supplier' => $supplierName,
                    'supplier_id' => $supplierId,
                    'created_at' => $attendance->created_at,
                    'verified_by_admin' => $attendance->is_present ? 1 : 0,
                    'is_present' => $attendance->is_present,
                    'price' => $attendable->price ?? 0,
                ];
            })->filter()->values();
    }

    protected function getOtherItems($siteId, $phaseName, $supplierName, $verificationStatus, $from_date = null, $to_date = null)
    {
        $items = collect();

        // Common filters
        $filters = function ($query) use ($siteId, $phaseName, $supplierName, $verificationStatus, $from_date, $to_date) {
            $query->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'));
            $query->whereHas('phase', fn($q) => $q->whereNull('deleted_at'));

            if ($siteId && $siteId !== 'all') {
                $query->whereHas('phase.site', fn($q) => $q->where('id', $siteId));
            }

            if ($phaseName && $phaseName !== 'all') {
                $query->whereHas('phase', fn($q) => $q->where('phase_name', $phaseName));
            }

            if ($supplierName && $supplierName !== 'all') {
                $query->whereHas('supplier', fn($q) => $q->where('name', $supplierName));
            }

            if ($verificationStatus && $verificationStatus !== 'all') {
                $query->where('verified_by_admin', $verificationStatus === 'verified' ? 1 : 0);
            }

            if ($from_date) {
                $query->whereDate('created_at', '>=', $from_date);
            }

            if ($to_date) {
                $query->whereDate('created_at', '<=', $to_date);
            }
        };

        // Construction Material Billing
        $items = $items->merge(
            ConstructionMaterialBilling::with(['phase.site', 'supplier'])
                ->where($filters)
                ->latest()
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'supplier' => $item->supplier->name ?? 'NA',
                    'description' => $item->item_name ?? 'NA',
                    'category' => 'Raw Material',
                    'phase' => $item->phase->phase_name ?? 'NA',
                    'site' => $item->phase->site->site_name ?? 'NA',
                    'site_id' => $item->phase->site_id ?? null,
                    'supplier_id' => $item->supplier_id ?? null,
                    'created_at' => $item->created_at,
                    'verified_by_admin' => $item->verified_by_admin,
                    'price' => $item->amount ?? null,
                ])
        );

        // Square Footage Bills
        $items = $items->merge(
            SquareFootageBill::with(['phase.site', 'supplier'])
                ->where($filters)
                ->latest()
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'supplier' => $item->supplier->name ?? 'NA',
                    'description' => $item->wager_name ?? 'NA',
                    'category' => 'Square Footage Bill',
                    'phase' => $item->phase->phase_name ?? 'NA',
                    'site' => $item->phase->site->site_name ?? 'NA',
                    'site_id' => $item->phase->site_id ?? null,
                    'supplier_id' => $item->supplier_id ?? null,
                    'created_at' => $item->created_at,
                    'verified_by_admin' => $item->verified_by_admin,
                    'price' => $item->price * $item->multiplier ?? null,
                ])
        );

        // Daily Expenses
        $items = $items->merge(
            DailyExpenses::with(['phase.site', 'supplier'])
                ->where($filters)
                ->latest()
                ->get()
                ->map(fn($item) => [
                    'id' => $item->id,
                    'supplier' => $item->supplier->name ?? 'NA',
                    'description' => $item->item_name ?? 'NA',
                    'category' => 'Daily Expense',
                    'phase' => $item->phase->phase_name ?? 'NA',
                    'site' => $item->phase->site->site_name ?? 'NA',
                    'site_id' => $item->phase->site_id ?? null,
                    'supplier_id' => $item->supplier_id ?? null,
                    'created_at' => $item->created_at,
                    'verified_by_admin' => $item->verified_by_admin,
                    'price' => $item->price ?? null,
                ])
        );

        return $items;
    }


    protected function getFilterOptions()
    {
        return [
            'sites' => Site::where('is_on_going', 1)->latest()->get(),
            'phases' => Phase::whereHas('site', fn($q) => $q->where('is_on_going', 1))
                ->pluck('phase_name')->unique(),
            'suppliers' => Supplier::whereNull('deleted_at')->pluck('name')->unique()
        ];
    }


}

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
        // Get filter parameters
        $siteId = $request->input('site_id');
        $phaseName = $request->input('phase');
        $supplierName = $request->input('supplier');
        $verificationStatus = $request->input('verification_status');

        // 1. Fetch attendance-based items (Wasta/Labour)
        $attendanceItems = $this->getAttendanceItems($siteId, $phaseName, $verificationStatus);

        // 2. Fetch other items (raw materials, bills, expenses)
        $otherItems = $this->getOtherItems($siteId, $phaseName, $supplierName, $verificationStatus);

        $data = $otherItems->merge($attendanceItems)->sortByDesc('created_at');

        // Get filter options
        $filterOptions = $this->getFilterOptions();

        // Paginate results
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

    protected function getAttendanceItems($siteId, $phaseName, $verificationStatus)
    {
        $query = Attendance::with([
            'attendable' => function ($query) {
                $query->with(['phase.site']);

                // Only load wasta relationship if attendable is Labour
                if ($query->getModel() instanceof Labour) {
                    $query->with('wasta');
                }
            }
        ])
            ->whereIn('attendable_type', [Wasta::class, Labour::class])
            ->whereHas('attendable', function ($query) use ($siteId, $phaseName) {
                $query->whereHas('phase.site', function ($query) use ($siteId) {
                    $query->whereNull('deleted_at');
                    if ($siteId && $siteId !== 'all') {
                        $query->where('id', $siteId);
                    }
                })
                    ->whereHas('phase', function ($query) use ($phaseName) {
                        $query->whereNull('deleted_at');
                        if ($phaseName && $phaseName !== 'all') {
                            $query->where('phase_name', $phaseName);
                        }
                    });
            });

        // Apply verification status filter
        if ($verificationStatus && $verificationStatus !== 'all') {
            $query->where('is_present', $verificationStatus === 'verified');
        }

        return $query->latest()
            ->get()
            ->map(function ($attendance) {
                $attendable = $attendance->attendable;

                if (!$attendable) {
                    return null;
                }

                $isWasta = $attendable instanceof Wasta;
                $isLabour = $attendable instanceof Labour;

                if (!$isWasta && !$isLabour) {
                    return null;
                }

                // For Labour, use the wasta relationship if loaded
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
                    'is_present' => $attendance->is_present
                ];
            })
            ->filter()
            ->values();
    }
    protected function getOtherItems($siteId, $phaseName, $supplierName, $verificationStatus)
    {
        $items = collect();

        // Raw Materials
        $items = $items->merge(
            ConstructionMaterialBilling::with(['phase.site', 'supplier'])
                ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
                ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
                ->when($siteId && $siteId !== 'all', fn($q) =>
                    $q->whereHas('phase.site', fn($sq) => $sq->where('id', $siteId)))
                ->when($phaseName && $phaseName !== 'all', fn($q) =>
                    $q->whereHas('phase', fn($pq) => $pq->where('phase_name', $phaseName)))
                ->when($supplierName && $supplierName !== 'all', fn($q) =>
                    $q->whereHas('supplier', fn($sq) => $sq->where('name', $supplierName)))
                ->when($verificationStatus && $verificationStatus !== 'all', fn($q) =>
                    $q->where('verified_by_admin', $verificationStatus === 'verified' ? 1 : 0))
                ->latest()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'supplier' => $item->supplier->name ?? 'NA',
                        'description' => $item->item_name ?? 'NA',
                        'category' => 'Raw Material',
                        'phase' => $item->phase->phase_name ?? 'NA',
                        'site' => $item->phase->site->site_name ?? 'NA',
                        'site_id' => $item->phase->site_id ?? null,
                        'supplier_id' => $item->supplier_id ?? null,
                        'created_at' => $item->created_at,
                        'verified_by_admin' => $item->verified_by_admin
                    ];
                })
        );

        // Square Footage Bills
        $items = $items->merge(
            SquareFootageBill::with(['phase.site', 'supplier'])
                ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
                ->whereHas('supplier', fn($q) => $q->whereNull('deleted_at'))
                ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
                ->when($siteId && $siteId !== 'all', fn($q) =>
                    $q->whereHas('phase.site', fn($sq) => $sq->where('id', $siteId)))
                ->when($phaseName && $phaseName !== 'all', fn($q) =>
                    $q->whereHas('phase', fn($pq) => $pq->where('phase_name', $phaseName)))
                ->when($supplierName && $supplierName !== 'all', fn($q) =>
                    $q->whereHas('supplier', fn($sq) => $sq->where('name', $supplierName)))
                ->when($verificationStatus && $verificationStatus !== 'all', fn($q) =>
                    $q->where('verified_by_admin', $verificationStatus === 'verified' ? 1 : 0))
                ->latest()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'supplier' => $item->supplier->name ?? 'NA',
                        'description' => $item->item_name ?? 'NA',
                        'category' => 'Square Footage Bill',
                        'phase' => $item->phase->phase_name ?? 'NA',
                        'site' => $item->phase->site->site_name ?? 'NA',
                        'site_id' => $item->phase->site_id ?? null,
                        'supplier_id' => $item->supplier_id ?? null,
                        'created_at' => $item->created_at,
                        'verified_by_admin' => $item->verified_by_admin
                    ];
                })
        );

        // Daily Expenses
        $items = $items->merge(
            DailyExpenses::with(['phase.site', 'supplier'])
                ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'))
                ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
                ->when($siteId && $siteId !== 'all', fn($q) =>
                    $q->whereHas('phase.site', fn($sq) => $sq->where('id', $siteId)))
                ->when($phaseName && $phaseName !== 'all', fn($q) =>
                    $q->whereHas('phase', fn($pq) => $pq->where('phase_name', $phaseName)))
                ->when($supplierName && $supplierName !== 'all', fn($q) =>
                    $q->whereHas('supplier', fn($sq) => $sq->where('name', $supplierName)))
                ->when($verificationStatus && $verificationStatus !== 'all', fn($q) =>
                    $q->where('verified_by_admin', $verificationStatus === 'verified' ? 1 : 0))
                ->latest()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'supplier' => $item->supplier->name ?? 'NA',
                        'description' => $item->item_name ?? 'NA',
                        'category' => 'Daily Expense',
                        'phase' => $item->phase->phase_name ?? 'NA',
                        'site' => $item->phase->site->site_name ?? 'NA',
                        'site_id' => $item->phase->site_id ?? null,
                        'supplier_id' => $item->supplier_id ?? null,
                        'created_at' => $item->created_at,
                        'verified_by_admin' => $item->verified_by_admin
                    ];
                })
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

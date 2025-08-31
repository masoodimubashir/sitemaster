<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{
    Attendance,
    ConstructionMaterialBilling,
    SquareFootageBill,
    DailyExpenses,
    Site,
    Phase,
    Supplier,
    Wasta,
    Wager
};

class   ItemsVerificationController extends Controller
{
    private const DEFAULT_PER_PAGE = 10;
    private const ALL_FILTER = 'all';

    public function index(Request $request)
    {
        $filters = $this->extractFilters($request);

        // Get all items with optimized queries
        $attendanceItems = $this->getAttendanceItems($filters);
        $otherItems = $this->getOtherItems($filters);

        // Merge and sort data
        $data = $otherItems->merge($attendanceItems)
                          ->sortByDesc('created_at')
                          ->values();

        // Get filter options for dropdowns
        $filterOptions = $this->getFilterOptions();

        // Create pagination
        $paginatedData = $this->createPagination($data, $request);

        return view("profile.partials.Admin.Site.show_unverified_items", array_merge(
            ['paginatedData' => $paginatedData],
            $filterOptions
        ));
    }

    private function extractFilters(Request $request): array
    {
        // Handle date filter conversions
        $dateFilter = $request->input('date_filter', 'lifetime');
        $fromDate = null;
        $toDate = null;

        switch ($dateFilter) {
            case 'today':
                $fromDate = $toDate = now()->format('Y-m-d');
                break;
            case 'yesterday':
                $fromDate = $toDate = now()->subDay()->format('Y-m-d');
                break;
            case 'this_week':
                $fromDate = now()->startOfWeek()->format('Y-m-d');
                $toDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $fromDate = now()->startOfMonth()->format('Y-m-d');
                $toDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $fromDate = now()->startOfYear()->format('Y-m-d');
                $toDate = now()->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                $fromDate = $request->input('start_date');
                $toDate = $request->input('end_date');
                break;
            case 'lifetime':
            default:
                // No date filters
                break;
        }

        return [
            'site_id' => $request->input('site_id', self::ALL_FILTER),
            'phase_name' => $request->input('phase', self::ALL_FILTER),
            'supplier_name' => $request->input('supplier', self::ALL_FILTER),
            'verification_status' => $request->input('verification_status', self::ALL_FILTER),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    private function getAttendanceItems(array $filters)
    {
        $query = Attendance::with([
            'attendanceSetup.setupable',
            'attendanceSetup.site.phases',
        ])
        ->whereHas('attendanceSetup', function ($q) use ($filters) {
            $this->applySiteFilter($q, $filters['site_id']);
            $this->applyPhaseFilter($q, $filters['phase_name']);
        });

        $this->applyVerificationFilter($query, $filters['verification_status'], 'is_present');
        $this->applyDateFilters($query, $filters['from_date'], $filters['to_date'], 'attendance_date');

        $results = $query->latest()->get();

        // Load wasta relationship for Wager models
        $results->each(function ($attendance) {
            $setupable = $attendance->attendanceSetup?->setupable;
            if ($setupable instanceof Wager && !$setupable->relationLoaded('wasta')) {
                $setupable->load('wasta');
            }
        });

        return $results->map(function ($attendance) {
            return $this->mapAttendanceItem($attendance);
        })->filter()->values();
    }

    private function getOtherItems(array $filters)
    {
        $items = collect();

        // Process each item type with optimized queries
        $itemTypes = [
            [
                'model' => ConstructionMaterialBilling::class,
                'category' => 'Raw Material',
                'description_field' => 'item_name',
                'price_calculation' => fn($item) => $item->amount
            ],
            [
                'model' => SquareFootageBill::class,
                'category' => 'Square Footage Bill',
                'description_field' => 'wager_name',
                'price_calculation' => fn($item) => ($item->price * $item->multiplier)
            ],
            [
                'model' => DailyExpenses::class,
                'category' => 'Daily Expense',
                'description_field' => 'item_name',
                'price_calculation' => fn($item) => $item->price
            ]
        ];

        foreach ($itemTypes as $itemType) {
            $items = $items->merge($this->getItemsByType($itemType, $filters));
        }

        return $items;
    }

    private function getItemsByType(array $itemType, array $filters)
    {
        $model = $itemType['model'];

        $query = $model::with(['phase.site', 'supplier'])
            ->whereHas('phase.site', fn($q) => $q->whereNull('deleted_at'))
            ->whereHas('phase', fn($q) => $q->whereNull('deleted_at'));

        // Apply all filters
        $this->applyCommonFilters($query, $filters);

        return $query->latest()->get()->map(function ($item) use ($itemType) {
            return $this->mapOtherItem($item, $itemType);
        });
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if ($this->isValidFilter($filters['site_id'])) {
            $query->whereHas('phase.site', fn($q) => $q->where('id', $filters['site_id']));
        }

        if ($this->isValidFilter($filters['phase_name'])) {
            $query->whereHas('phase', fn($q) => $q->where('phase_name', $filters['phase_name']));
        }

        if ($this->isValidFilter($filters['supplier_name'])) {
            $query->whereHas('supplier', fn($q) => $q->where('name', $filters['supplier_name']));
        }

        $this->applyVerificationFilter($query, $filters['verification_status'], 'verified_by_admin');
        $this->applyDateFilters($query, $filters['from_date'], $filters['to_date']);
    }

    private function applySiteFilter($query, $siteId): void
    {
        if ($this->isValidFilter($siteId)) {
            $query->where('site_id', $siteId);
        }
    }

    private function applyPhaseFilter($query, $phaseName): void
    {
        if ($this->isValidFilter($phaseName)) {
            $query->whereHas('site.phases', fn($q) => $q->where('phase_name', $phaseName));
        }
    }

    private function applyVerificationFilter($query, $status, string $field = 'verified_by_admin'): void
    {
        if ($this->isValidFilter($status)) {
            $isVerified = $status === 'verified';
            $query->where($field, $isVerified ? 1 : 0);
        }
    }

    private function applyDateFilters($query, $fromDate, $toDate, string $dateField = 'created_at'): void
    {
        if ($fromDate) {
            $query->whereDate($dateField, '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate($dateField, '<=', $toDate);
        }
    }

    private function isValidFilter($value): bool
    {
        return $value && $value !== self::ALL_FILTER;
    }

    private function mapAttendanceItem($attendance): ?array
    {
        $setup = $attendance->attendanceSetup;
        if (!$setup) {
            return null;
        }

        $setupable = $setup->setupable; // Wasta or Wager (already eager loaded)

        // Handle the case where setupable is Wager and we need wasta
        $supplier = null;
        $supplierId = null;

        if ($setupable instanceof Wager) {
            // The wasta relationship should be eager loaded now
            $supplier = $setupable->wasta;
            $supplierId = $setupable->wasta_id;
        }

        return [
            'id' => $attendance->id,
            'phase' => $setup->site->phases->pluck('phase_name')->implode(', ') ?? 'NA',
            'description' => $setupable->wasta_name ?? $setupable->wager_name ?? 'NA',
            'site' => $setup->site->site_name ?? 'NA',
            'category' => 'Attendance',
            'site_id' => $setup->site_id,
            'supplier' => $supplier,
            'supplier_id' => $supplierId,
            'created_at' => $attendance->created_at,
            'verified_by_admin' => $attendance->is_present ? 1 : 0,
            'is_present' => $attendance->is_present,
            'price' => $setup->price ?? 0,
            'debit' => $setup->price ?? 0, // Add debit field for view compatibility
        ];
    }

    private function mapOtherItem($item, array $itemType): array
    {
        $descriptionField = $itemType['description_field'];
        $priceCalculation = $itemType['price_calculation'];
        $calculatedPrice = $priceCalculation($item) ?? 0;

        return [
            'id' => $item->id,
            'supplier' => $item->supplier->name ?? 'NA',
            'description' => $item->$descriptionField ?? 'NA',
            'category' => $itemType['category'],
            'phase' => $item->phase->phase_name ?? 'NA',
            'site' => $item->phase->site->site_name ?? 'NA',
            'site_id' => $item->phase->site_id ?? null,
            'supplier_id' => $item->supplier_id ?? null,
            'created_at' => $item->created_at,
            'verified_by_admin' => $item->verified_by_admin,
            'price' => $calculatedPrice,
            'debit' => $calculatedPrice, // Add debit field for view compatibility
        ];
    }

    private function createPagination($data, Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->get('per_page', self::DEFAULT_PER_PAGE);
        $currentPage = (int) $request->input('page', 1);

        return new LengthAwarePaginator(
            $data->forPage($currentPage, $perPage),
            $data->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
    }

    private function getFilterOptions(): array
    {
        return [
            'sites' => Site::where('is_on_going', 1)->latest()->get(),
            'phases' => Phase::whereHas('site', fn($q) => $q->where('is_on_going', 1))
                             ->pluck('phase_name')
                             ->unique()
                             ->values(),
            'suppliers' => Supplier::whereNull('deleted_at')
                                  ->pluck('name')
                                  ->unique()
                                  ->values()
        ];
    }

    /**
     * Verify/Unverify Daily Expenses
     */
    public function verifyExpense(Request $request, $id)
    {
        try {
            $expense = DailyExpenses::findOrFail($id);
            $expense->verified_by_admin = $request->input('verified', 1);
            $expense->save();

            return response()->json([
                'success' => true,
                'message' => $expense->verified_by_admin ? 'Expense verified successfully!' : 'Expense unverified successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify/Unverify Raw Materials
     */
    public function verifyMaterial(Request $request, $id)
    {
        try {
            $material = ConstructionMaterialBilling::findOrFail($id);
            $material->verified_by_admin = $request->input('verified', 1);
            $material->save();

            return response()->json([
                'success' => true,
                'message' => $material->verified_by_admin ? 'Material verified successfully!' : 'Material unverified successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify/Unverify Square Footage Bills
     */
    public function verifySquareFootage(Request $request, $id)
    {
        try {
            $squareFootage = SquareFootageBill::findOrFail($id);
            $squareFootage->verified_by_admin = $request->input('verified', 1);
            $squareFootage->save();

            return response()->json([
                'success' => true,
                'message' => $squareFootage->verified_by_admin ? 'Square footage bill verified successfully!' : 'Square footage bill unverified successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify/Unverify Attendance
     */
    public function verifyAttendance(Request $request, $id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->is_present = $request->input('verified', 1);
            $attendance->save();

            return response()->json([
                'success' => true,
                'message' => $attendance->is_present ? 'Attendance verified successfully!' : 'Attendance unverified successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}

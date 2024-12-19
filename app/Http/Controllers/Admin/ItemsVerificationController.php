<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\SquareFootageBill;
use App\Models\WagerAttendance;
use App\Services\DataService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ItemsVerificationController extends Controller
{
    public function index(Request $request, DataService $dataService)
    {


        $raw_materials = ConstructionMaterialBilling::with(['phase.site', 'supplier'])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplier) {
                $supplier->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })->when(request('site_id') && request('site_id') !== 'all', function ($query) {
                return $query->whereHas('phase.site', function ($siteQuery) {
                    $siteQuery->where('id', request('site_id'));
                });
            })
            ->latest()
            ->get();

        $squareFootageBills = SquareFootageBill::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    }
                ])->withoutTrashed();
            },
            'supplier' => function ($supplier) {
                $supplier->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('supplier', function ($supplier) {
                $supplier->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })->when(request('site_id') && request('site_id') !== 'all', function ($query) {
                return $query->whereHas('phase.site', function ($siteQuery) {
                    $siteQuery->where('id', request('site_id'));
                });
            })
            ->latest()
            ->get();



        $expenses = DailyExpenses::with([
            'phase' => function ($phase) {
                $phase->with([
                    'site' => function ($site) {
                        $site->withoutTrashed();
                    }
                ])->withoutTrashed();
            },
        ])
            ->whereHas('phase', function ($phase) {
                $phase->whereNull('deleted_at');
            })
            ->whereHas('phase.site', function ($site) {
                $site->whereNull('deleted_at');
            })->when(request('site_id') && request('site_id') !== 'all', function ($query) {
                return $query->whereHas('phase.site', function ($siteQuery) {
                    $siteQuery->where('id', request('site_id'));
                });
            })
            ->latest()
            ->get();

        // $wagers = DailyWager::with([
        //     'phase' => function ($phase) {
        //         $phase->with([
        //             'site' => function ($site) {
        //                 $site->withoutTrashed();
        //             },
        //             'wagerAttendances'
        //         ])->withoutTrashed();
        //     },
        //     'supplier' => function ($supplier) {
        //         $supplier->withoutTrashed();
        //     },
        // ])
        //     ->whereHas('phase', function ($phase) {
        //         $phase->whereNull('deleted_at');
        //     })
        //     ->whereHas('supplier', function ($supplier) {
        //         $supplier->whereNull('deleted_at');
        //     })
        //     ->whereHas('phase.site', function ($site) {
        //         $site->whereNull('deleted_at');
        //     })->when(request('supplier_id') && request('supplier_id') != 'all', function ($phaseuery) {
        //         return $phaseuery->where('supplier_id', request('supplier_id'));
        //     })
        //     ->latest()
        //     ->get();


        $wager_attendance = WagerAttendance::with('dailyWager')
            ->when(request('site_id') && request('site_id') !== 'all', function ($query) {
                return $query->whereHas('phase.site', function ($siteQuery) {
                    $siteQuery->where('id', request('site_id'));
                });
            })
            ->latest()
            ->get();


        $data = collect();

        $data = $data->merge($raw_materials->map(function ($material) {

            return [
                'id' => $material->id,
                'supplier' => $material->supplier->name ?? 0,
                'description' => $material->item_name ?? 0,
                'category' => 'Raw Material',
                'phase' => $material->phase->phase_name ?? 0,
                'site' => $material->phase->site->site_name ?? 0,
                'site_id' => $material->phase->site_id ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'created_at' => $material->created_at,

                'verified_by_admin' => $material->verified_by_admin
            ];
        }));

        $data = $data->merge($squareFootageBills->map(function ($bill) {


            return [
                'id' => $bill->id,
                'supplier' => $bill->supplier->name ?? 0,
                'description' => $bill->wager_name ?? 0,
                'category' => 'Square Footage Bill',
                'phase' => $bill->phase->phase_name ?? 0,
                'site' => $bill->phase->site->site_name ?? 0,
                'site_id' => $bill->phase->site_id ?? null,
                'supplier_id' => $bill->supplier_id ?? null,
                'created_at' => $bill->created_at,
                'verified_by_admin' => $bill->verified_by_admin
            ];
        }));

        $data = $data->merge($expenses->map(function ($expense) {

            return [
                'id' => $expense->id,
                'description' => $expense->item_name ?? null,
                'category' => 'Daily Expense',
                'phase' => $expense->phase->phase_name ?? 0,
                'site' => $expense->phase->site->site_name ?? 0,
                'site_id' => $expense->phase->site_id ?? null,
                'supplier_id' => $expense->supplier_id ?? null,
                'supplier' => $expense->supplier->name ?? 'NA',
                'created_at' => $expense->created_at,
                'verified_by_admin' => $expense->verified_by_admin
            ];
        }));

        $data = $data->merge($wager_attendance->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'phase' => $attendance->phase->phase_name ?? 'NA',
                'description' => $attendance->dailyWager->wager_name ?? 'NA',
                'site' => $attendance->phase->site->site_name,
                'category' => 'Attendance',
                'site_id' => $attendance->phase->site_id,
                'supplier' => $attendance->dailyWager->supplier->name,
                'supplier_id' => $attendance->dailyWager->supplier_id,
                'created_at' => $attendance->created_at,
                'verified_by_admin' => $attendance->verified_by_admin,
            ];
        }));

        // $data = $data->merge($wagers->map(function ($wager) {
        //             return [
        //                 'id' => $wager->id,
        //                 'supplier' => $wager->supplier->name ?? '',
        //                 'description' => $wager->wager_name . 'ATTD' ?? 0,
        //                 'category' => 'Daily Wager',
        //                 'phase' => $wager->phase->phase_name ?? 0,
        //                 'site' => $wager->phase->site->site_name ?? 0,
        //                 'site_id' => $wager->phase->site_id ?? null,
        //                 'supplier_id' => $wager->supplier_id ?? null,
        //                 'created_at' => $wager->created_at,
        //                 'verified_by_admin' => $wager->verified_by_admin
        //             ];
        //         })
        // );

        $perPage = $request->get('per_page', 20);

        $paginatedData = new LengthAwarePaginator(
            $data->forPage($request->input('page', 1), $perPage),
            $data->count(),
            $perPage,
            $request->input('page', 10),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $sites = $paginatedData->unique('site_id');

        return view("profile.partials.Admin.Site.show_unverified_items", compact(
            'paginatedData',
            'sites',
        ));
    }

    public function verifyItems() {}
}

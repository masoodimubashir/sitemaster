<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Client;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DataService;

class DashboardController extends Controller
{
    public function siteDashboard(DataService $dataService)
    {


//        phpinfo();
//        xdebug_info();

        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $startDate = null;
        $endDate = null;
        $phase_id = 'all';
        $site_id = 'all';

        [$payments, $raw_materials, $squareFootageBills, $expenses, $attendances] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $attendances
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate overall balances
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $total_paid = $withServiceCharge['paid'];
        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];
        $returns = $withoutServiceCharge['return'];

        // Use separate queries to avoid mutation
        $ongoingSitesCount = Site::where('is_on_going', 1)->count();
        $completedSitesCount = Site::where('is_on_going', 0)->count();

        $search = request('search');

        $sitesQuery = Site::query();

        if ($search) {
            $sitesQuery->where(function ($query) use ($search): void {
                $query->where('site_name', 'like', '%' . $search . '%')
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }


        $sites = $sitesQuery
            ->with(['client', 'users'])
            ->where('is_on_going', 1)
            ->paginate(10);


        // Calculate site-wise balances using DataService
        $sites->getCollection()->transform(function ($site) use ($dataService) {
            $siteFilter = 'lifetime';
            $supplierFilter = 'all';
            $phaseFilter = 'all';

            // Get data for this specific site
            [$sitePayments, $siteRawMaterials, $siteSquareFootageBills, $siteExpenses,$siteAttendances] = $dataService->getData(
                $siteFilter,
                $site->id, // Filter by this specific site
                $supplierFilter,
                null, // startDate
                null, // endDate
                $phaseFilter
            );

            // Transform data into ledger format
            $siteLedgers = $dataService->makeData(
                $sitePayments,
                $siteRawMaterials,
                $siteSquareFootageBills,
                $siteExpenses,
                $siteAttendances
            );

            // Calculate balances for this site
            $siteBalances = $dataService->calculateAllBalances($siteLedgers);

            // Extract the required values
            $site->total_paid = $siteBalances['with_service_charge']['paid']; // Sum of payments
            $site->total_due = $siteBalances['with_service_charge']['due'];   // Sum of all expenses/costs
            $site->total_balance = $siteBalances['with_service_charge']['balance']; // Due - Paid - Returns
            $site->total_return = $siteBalances['with_service_charge']['return'];

            // Additional breakdown for detailed view
            $site->balance_breakdown = [
                'materials' => $siteLedgers->where('category', 'Material')->sum('total_amount_with_service_charge'),
                'expenses' => $siteLedgers->where('category', 'Expense')->sum('total_amount_with_service_charge'),
                'sqft' => $siteLedgers->where('category', 'SQFT')->sum('total_amount_with_service_charge'),
                'attendance' => $siteLedgers->where('category', 'Attendance')->sum('total_amount_with_service_charge'),
                'payments' => $siteLedgers->where('category', 'Payment')->sum('credit'),
                'returns' => $siteLedgers->where('category', 'Payment')->sum('return'),
            ];

            return $site;
        });


        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::orderBy('name')->get();


        return view('profile.partials.Admin.Dashboard.dashboard', [
            'ongoingSites' => $ongoingSitesCount,
            'completedSites' => $completedSitesCount,
            'sites' => $sites,
            'users' => $users,
            'clients' => $clients,
            // Overall totals
            'overall_paid' => $total_paid,
            'overall_due' => $total_due,
            'overall_balance' => $total_balance,
            'overall_returns' => $returns,
        ]);
    }

    public function supplierDashboard(DataService $dataService)
    {
        $dateFilter = 'lifetime';
        $supplier_id = 'all';
        $startDate = null;
        $endDate = null;
        $phase_id = 'all';
        $site_id = 'all';


        [$payments, $raw_materials, $squareFootageBills, $expenses, $attendances] = $dataService->getData(
            $dateFilter,
            $site_id,
            $supplier_id,
            $startDate,
            $endDate,
            $phase_id
        );

        $ledgers = $dataService->makeData(
            $payments,
            $raw_materials,
            $squareFootageBills,
            $expenses,
            $attendances
        )->sortByDesc(function ($d) {
            return $d['created_at'];
        });

        // Calculate balances (WITHOUT service charge)
        $balances = $dataService->calculateAllBalances($ledgers);
        $withoutServiceCharge = $balances['without_service_charge'];
        $total_paid = $withoutServiceCharge['paid'];
        $total_due = $withoutServiceCharge['due'];
        $total_balance = $withoutServiceCharge['balance'];
        $returns = $withoutServiceCharge['return'];

        // Apply search filter
        $search = request('search');

        $suppliersQuery = Supplier::query();

        if ($search) {
            $suppliersQuery->where('name', 'like', '%' . $search . '%');
        }

        // Fetch suppliers and calculate balances per supplier
        $suppliers = $suppliersQuery
            ->paginate(10);

        $suppliers->getCollection()->transform(function ($supplier) use ($dataService) {
            $supplierFilter = 'lifetime';
            $phaseFilter = 'all';

            // Fetch data for this supplier (NO attendance)
            [$payments, $raw_materials, $squareFootageBills, $expenses] = $dataService->getData(
                $supplierFilter,
                'all', // site filter not needed here
                $supplier->id, // supplier filter
                null,
                null,
                $phaseFilter
            );

            $ledgers = $dataService->makeData(
                $payments,
                $raw_materials,
                $squareFootageBills,
                $expenses,
            );

            // Calculate balances (NO service charge)
            $balances = $dataService->calculateAllBalances($ledgers);
            $withoutServiceCharge = $balances['without_service_charge'];

            $supplier->total_paid = $withoutServiceCharge['paid'];
            $supplier->total_due = $withoutServiceCharge['due'];
            $supplier->total_balance = $withoutServiceCharge['balance'];
            $supplier->total_return = $withoutServiceCharge['return'];

            // Breakdown
            $supplier->balance_breakdown = [
                'materials' => $ledgers->where('category', 'Material')->sum('total_amount_with_service_charge'),
                'expenses' => $ledgers->where('category', 'Expense')->sum('total_amount_with_service_charge'),
                'sqft' => $ledgers->where('category', 'SQFT')->sum('total_amount_with_service_charge'),
                'attendance' => $ledgers->where('category', 'Attendance')->sum('total_amount_with_service_charge'),
                'payments' => $ledgers->where('category', 'Payment')->sum('credit'),
                'returns' => $ledgers->where('category', 'Payment')->sum('return'),
            ];

            return $supplier;
        });

        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::orderBy('name')->get();

        return view('profile.partials.admin.dashboard.SupplierDashboard', [
            'suppliers' => $suppliers,
            'users' => $users,
            'clients' => $clients,
            'overall_paid' => $total_paid,
            'overall_due' => $total_due,
            'overall_balance' => $total_balance,
            'overall_returns' => $returns,
        ]);
    }

}

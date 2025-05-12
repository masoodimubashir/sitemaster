<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Charts\BalancePaidChart;
use App\Charts\CostProfitChart;
use App\Charts\PaymentChart;
use App\Http\Requests\StoreSiteRequest;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\UserSiteNotification;
use App\Services\DataService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function siteDashboard(Request $request)
    {
        // Use separate queries to avoid mutation
        $ongoingSitesCount = Site::where('is_on_going', 1)->count();
        $completedSitesCount = Site::where('is_on_going', 0)->count();

        // Base query for pagination
        $search = request('search');

        $sitesQuery = Site::query();

        if ($search) {
            $sitesQuery->whereHas('client', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $sites = $sitesQuery
            ->withSum([
                'payments as total_payments' => fn($pay) => $pay->where([
                    ['verified_by_admin', '=', 1],
                    ['transaction_type', '=', 0],
                ]),
                'payments as total_site_expenses_from_payments' => fn($pay) => $pay->where([
                    ['verified_by_admin', '=', 1],
                    ['transaction_type', '=', 1],
                ]),
            ], 'amount')
            ->with([
                'client',
                'phases' => function ($query) {
                    $query
                        ->withSum([
                            'constructionMaterialBillings as total_material_billing' => fn($q) => $q->where('verified_by_admin', 1)
                        ], 'amount')
                        ->withSum([
                            'squareFootageBills as total_square_footage' => fn($q) => $q->where('verified_by_admin', 1)
                        ], 'price')
                        ->withSum('dailyWagers as total_daily_wagers', 'price_per_day')
                        ->withSum([
                            'dailyExpenses as total_daily_expenses' => fn($q) => $q->where('verified_by_admin', 1)
                        ], 'price');
                }
            ])
            ->paginate(10);

        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::orderBy('name')->get();


        return view('profile.partials.Admin.Dashboard.dashboard', [
            'ongoingSites' => $ongoingSitesCount,
            'completedSites' => $completedSitesCount,
            'sites' => $sites,
            'users' => $users,
            'clients' => $clients,
        ]);
    }



    public function supplierDashboard()
    {

        $search = request('search');

        $suppliersQuery = Supplier::query();

        if ($search) {
            $suppliersQuery->where('name', 'like', '%' . $search . '%');
        }

        $suppliers = $suppliersQuery
            ->withSum([
                'payments as total_site_expenses_from_payments' => fn($q) => $q
                    ->where('verified_by_admin', 1)
                    ->where('transaction_type', 1)
            ], 'amount')
            ->withSum([
                'constructionMaterialBilling as total_material_billing' => fn($q) => $q
                    ->where('verified_by_admin', 1)
            ], 'amount')
            ->withSum([
                'squareFootages as total_square_footage' => fn($q) => $q
                    ->where('verified_by_admin', 1)
            ], 'price')
            ->withSum('dailyWagers as total_daily_wagers', 'price_per_day')
            ->withSum([
                'payments as total_income_payments' => fn($q) => $q
                    ->where('verified_by_admin', 1)
                    ->where('transaction_type', 0)
            ], 'amount')
            ->paginate(10);

        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::orderBy('name')->get();

        return view('profile.partials.admin.dashboard.SupplierDashboard', [
            'suppliers' => $suppliers,
            'users' => $users,
            'clients' => $clients,
        ]);
    }
}

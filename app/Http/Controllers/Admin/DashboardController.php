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
use Request;

class DashboardController extends Controller
{
    public function dashboard(DataService $dataService)
    {

        $sitesQuery = Site::latest();
        $ongoingSitesCount = (clone $sitesQuery)->where('is_on_going', 1)->count();
        $completedSitesCount = (clone $sitesQuery)->where('is_on_going', 0)->count();

        $users = User::where('role_name', 'site_engineer')->get();
        $clients = Client::all();

        $sites = $sitesQuery
            ->withSum([
                'payments as total_payments' => fn($pay) => $pay->where('verified_by_admin', 1)
            ], 'amount')
            ->with(['client', 'phases' => function ($query) {
                $query->withSum([
                    'constructionMaterialBillings as total_material_billing' => fn($q) => $q->where('verified_by_admin', 1)
                ], 'amount')
                    ->withSum([
                        'squareFootageBills as total_square_footage' => fn($q) => $q->where('verified_by_admin', 1)
                    ], 'price')
                    ->withSum(
                        'dailyWagers as total_daily_wagers',
                        'price_per_day'
                    )
                    ->withSum([
                        'dailyExpenses as total_daily_expenses' => fn($q) => $q->where('verified_by_admin', 1)
                    ], 'price');
            }])
            ->paginate(10);


        return view('profile.partials.Admin.Dashboard.dashboard', [
            'ongoingSites' => $ongoingSitesCount,
            'completedSites' => $completedSitesCount,
            'sites' => $sites,
            'users' => $users,
            'clients' => $clients,
        ]);
    }


   
}

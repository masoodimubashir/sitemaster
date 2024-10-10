<?php

namespace App\Http\Controllers\Admin;

use App\Charts\SiteChart;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function dashboard()
    {
        // $site = Site::with('paymentSuppliers')->find(1);

        // $site_chart = new SiteChart();

        // if (!$site) {
        //     return view('dashboard', compact('site_chart'));
        // }

        // // Ensure paymentSuppliers is available
        // $monthlyRates = $site->paymentSuppliers
        // ->groupBy(function ($billing) {
        //     return $billing->created_at->format('M');
        // })
        // ->map(function ($billings) {
        //     return $billings->sum('amount');
        // });

        // // Set up the chart with monthly rates
        // $site_chart->labels($monthlyRates->keys());
        // $site_chart->dataset('Monthly Rates', 'line', $monthlyRates->values());

        return view('profile.partials.Admin.Dashboard.dashboard');

    }

    public function dashboardUsers(User $user){
        $users = $user->latest()->limit(10)->get();


        return view('components.dashboard-users', compact('users'));
    }
}

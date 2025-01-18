<?php

namespace App\Http\Controllers\Admin;

use App\Charts\BalancePaidChart;
use App\Charts\CostProfitChart;
use App\Charts\PaymentChart;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ConstructionMaterialBilling;
use App\Models\DailyExpenses;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use App\Models\WagerAttendance;
use App\Services\DataService;

class DashboardController extends Controller
{
    public function dashboard(DataService $dataService)
    {

        //  Get Notification Of the Authenticated User
        $notifications = auth()->user()->unreadNotifications;

        //  Get All The Data
        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] = $dataService->getData('lifetime', 'all', 'all', 'all');

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);


        
        $balances = $dataService->calculateAllBalances($ledgers);

        // Access the values
        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];

        // Get specific totals
        $balance_without_service_charge = $withoutServiceCharge['balance'];
        $balance_with_service_charge = $withServiceCharge['balance'];


        $paid = $withoutServiceCharge['paid'];

        $total_due = $withServiceCharge['due'];
        $total_balance = $withServiceCharge['balance'];



        // $data = $dataService->calculateBalancesWithServiceCharge($ledgers);

        // $paid = $total_paid;
        // $balance_without_service_charge = $total_balance;
        // $balance_with_service_charge = $data[2];

        $balance_paid_chart = new BalancePaidChart($balance_without_service_charge, $paid);

        $revenue = $balance_without_service_charge + $paid;
        $profit = $revenue - $balance_with_service_charge;

        $cost_profit_chart = new CostProfitChart($revenue, $profit);

        $payment_chart = new PaymentChart($payments);

        $clients = Client::all();
        $suppliers = Supplier::all();
        $payments = PaymentSupplier::with('supplier', 'site')->get();
       

        // // Get all sites with opened and closed counts
        $opened_sites = Site::where('is_on_going', 1)->count();
        $closed_sites = Site::where('is_on_going', 0)->count();

        $data = collect();

        $data = $data->merge($suppliers->map(function ($supplier) {
            return [
                'suppliers' => $supplier ?? 'NA',
                'category' => 'Suppliers',
            ];
        }));

        $data = $data->merge($clients->map(function ($client) {
            return [
                'clients' => $client ?? 'NA',
                'category' => 'Clients',
            ];
        }));


        $data = $data->merge($payments->map(function ($pay) use (&$sum_total_payment_amount) {
            // Update the running total
            $sum_total_payment_amount += $pay->amount ?? 0;

            return [

                'amount' => $pay->amount ?? 0,
                'screenshot' => $pay->screenshot ?? 'NA',
                'supplier' => $pay->supplier->name ?? '',
                'description' => $pay->item_name ?? 'NA',
                'category' => 'Payments',
                'debit' => 'NA',
                'credit' => $pay->amount,
                'phase' => $pay->phase->phase_name ?? 'NA',
                'site' => $pay->site->site_name ?? 'NA',
                'site_service_charge' => $pay->phase->site->service_charge ?? 0,
                'site_id' => $pay->site_id ?? null,
                'supplier_id' => $pay->supplier_id ?? null,
                'created_at' => $pay->created_at,
                'sum_total_payment_amount' => $sum_total_payment_amount
            ];
        }));


        return view('profile.partials.Admin.Dashboard.dashboard',
            compact('data', 'balance_paid_chart', 'opened_sites', 'closed_sites', 'notifications', 'cost_profit_chart', 'payment_chart', 'paid')
        );
    }
}

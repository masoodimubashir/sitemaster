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

class DashboardController extends Controller
{
    public function dashboard()
    {

        $sum_total_payment_amount = 0;
        $clients = Client::all();
        $suppliers = Supplier::all();
        $payments = PaymentSupplier::with('supplier', 'site')->get();
        $rawMaterials = ConstructionMaterialBilling::with('phase')->get();
        $squareFootageBills = SquareFootageBill::with('phase')->get();
        $expenses = DailyExpenses::with('phase')->get();
        $dailyWagers = DailyWager::with('phase')->get();
        $wager_attendances = WagerAttendance::all();

        $notifications = auth()->user()->unreadNotifications;

        // Get all sites with opened and closed counts
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

        // Merge raw materials into data
        $data = $data->merge($rawMaterials->map(function ($material) {
            return [
                'amount' => $material->amount ?? 0,
                'supplier' => $material->supplier->name ?? 'NA',
                'description' => $material->item_name ?? 'NA',
                'category' => 'Raw Material',
                'debit' => $material->amount,
                'credit' => 'NA',
                'phase' => $material->phase->phase_name ?? 'NA',
                'site' => $material->phase->site->site_name ?? 'NA',
                'site_service_charge' => $material->phase->site->service_charge ?? 0,
                'site_id' => $material->phase->site_id ?? null,
                'supplier_id' => $material->supplier_id ?? null,
                'created_at' => $material->created_at,
            ];
        }));

        // Merge square footage bills into data
        $data = $data->merge($squareFootageBills->map(function ($bill) {
            return [
                'amount' => $bill->price ?? 0,
                'supplier' => $bill->supplier->name ?? 'NA',
                'description' => $bill->wager_name ?? 'NA',
                'category' => 'Square Footage Bill',
                'debit' => $bill->price,
                'credit' => 'NA',
                'phase' => $bill->phase->phase_name ?? 'NA',
                'site' => $bill->phase->site->site_name ?? 'NA',
                'site_service_charge' => $bill->phase->site->service_charge ?? 0,
                'site_id' => $bill->phase->site_id ?? null,
                'supplier_id' => $bill->supplier_id ?? null,
                'created_at' => $bill->created_at,
            ];
        }));

        // Merge daily expenses into data
        $data = $data->merge($expenses->map(function ($expense) {
            return [
                'amount' => $expense->price ?? 0,
                'supplier' => $expense->supplier->name ?? '',
                'description' => $expense->item_name ?? 'NA',
                'category' => 'Daily Expense',
                'debit' => $expense->price,
                'credit' => 'NA',
                'phase' => $expense->phase->phase_name ?? 'NA',
                'site' => $expense->phase->site->site_name ?? 'NA',
                'site_service_charge' => $expense->phase->site->service_charge ?? 0,
                'site_id' => $expense->phase->site_id ?? null,
                'supplier_id' => $expense->supplier_id ?? null,
                'created_at' => $expense->created_at,
            ];
        }));

        // Merge daily wagers into data
        $data = $data->merge($dailyWagers->map(function ($wager) use ($wager_attendances) {
            return [
                'amount' => $wager->price_per_day ?? 0,
                'supplier' => $wager->supplier->name ?? '',
                'description' => $wager->wager_name ?? 'NA',
                'category' => 'Daily Wager',
                'debit' => $wager->phase->wagerAttendances->sum('no_of_persons') * $wager->price_per_day,
                'credit' => 'NA',
                'phase' => $wager->phase->phase_name ?? 'NA',
                'site' => $wager->phase->site->site_name ?? 'NA',
                'site_service_charge' => $wager->phase->site->service_charge ?? 0,
                'site_id' => $wager->phase->site_id ?? null,
                'supplier_id' => $wager->supplier_id ?? null,
                'created_at' => $wager->created_at,
            ];
        }));

        $totalPaymentAmount = $sum_total_payment_amount;

        $totalAmount = $data->filter(function ($d) {
            return ($d['category'] !== 'Suppliers' && $d['category'] !== 'Clients') && $d['phase'] !== 'NA';
        })->sum('amount');

        $filteredData = $data->filter(function ($d) {
            return ($d['category'] !== 'Suppliers' && $d['category'] !== 'Clients') && $d['phase'] !== 'NA';
        });

        $payments = $data->filter(function ($d) {
            return $d['category'] === 'Payments';
        });

        $paymentsTotalAmount = $payments->sum('amount');

        $totalAmountWithServiceChargeAmount = 0;

        foreach ($filteredData as $record) {

            $serviceChargeAmount = ($record['amount'] * $record['site_service_charge']);

            $totalAmount = $record['amount'] + $serviceChargeAmount / 100;

            $totalAmountWithServiceChargeAmount += $totalAmount;
        }

        $balance = $totalAmount - $totalPaymentAmount;
        $revenue = $totalAmountWithServiceChargeAmount + $totalPaymentAmount;
        $expenses = $balance + $totalPaymentAmount;

        $balance_paid_chart = new BalancePaidChart($balance, $totalPaymentAmount);
        $cost_profit_chart = new CostProfitChart($expenses, $revenue);
        $payment_chart = new PaymentChart($payments);

        return view(
            'profile.partials.Admin.Dashboard.dashboard',
            compact('data', 'balance_paid_chart', 'opened_sites', 'closed_sites', 'notifications', 'cost_profit_chart', 'payment_chart', 'paymentsTotalAmount')
        );
    }
}

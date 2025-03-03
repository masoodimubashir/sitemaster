<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Charts\BalancePaidChart;
use App\Charts\CostProfitChart;
use App\Charts\PaymentChart;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Site;
use App\Models\Supplier;
use App\Services\DataService;

class DashboardController extends Controller
{
    public function dashboard(DataService $dataService)
    {

        $notifications = auth()->user()->unreadNotifications;

        $siteStats = $this->getSiteStatistics();

        $financialData = $this->getFinancialData($dataService);

        $charts = $this->prepareCharts(
            $financialData['balance_without_service_charge'],
            $financialData['paid'],
            $financialData['revenue'],
            $financialData['profit'],
            $financialData['payments']
        );

        $entityData = $this->getEntityData();

        $data = $this->consolidateData($entityData);

        return view('profile.partials.Admin.Dashboard.dashboard', [
            'data' => $data,
            'notifications' => $notifications,
            'opened_sites' => $siteStats['opened'],
            'closed_sites' => $siteStats['closed'],
            'paid' => $financialData['paid'],
            'balance_paid_chart' => $charts['balance_paid'],
            'cost_profit_chart' => $charts['cost_profit'],
            'payment_chart' => $charts['payment']
        ]);
    }

    /**
     * Get site statistics
     *
     * @return array
     */
    private function getSiteStatistics()
    {
        return [
            'opened' => Site::where('is_on_going', 1)->count(),
            'closed' => Site::where('is_on_going', 0)->count()
        ];
    }

    /**
     * Get financial data from the DataService
     *
     * @param DataService $dataService
     * @return array
     */
    private function getFinancialData(DataService $dataService)
    {

        [$payments, $raw_materials, $squareFootageBills, $expenses, $wagers] =
            $dataService->getData('lifetime', 'all', 'all', 'all');

        $ledgers = $dataService->makeData($payments, $raw_materials, $squareFootageBills, $expenses, $wagers);
        $balances = $dataService->calculateAllBalances($ledgers);

        $withoutServiceCharge = $balances['without_service_charge'];
        $withServiceCharge = $balances['with_service_charge'];
        $balance_without_service_charge = $withoutServiceCharge['balance'];
        $balance_with_service_charge = $withServiceCharge['balance'];
        $paid = $withoutServiceCharge['paid'];

        $revenue = $balance_with_service_charge + $paid;
        $profit = $revenue - $balance_with_service_charge;

        return [
            'payments' => $payments,
            'balance_without_service_charge' => $balance_without_service_charge,
            'balance_with_service_charge' => $balance_with_service_charge,
            'paid' => $paid,
            'revenue' => $revenue,
            'profit' => $profit
        ];
    }

    /**
     * Prepare chart objects
     *
     * @param float $balance
     * @param float $paid
     * @param float $revenue
     * @param float $profit
     * @param array $payments
     * @return array
     */
    private function prepareCharts($balance, $paid, $revenue, $profit, $payments)
    {
        return [
            'balance_paid' => new BalancePaidChart($balance, $paid),
            'cost_profit' => new CostProfitChart($revenue, $profit),
            'payment' => new PaymentChart($payments)
        ];
    }

    /**
     * Get entity data (clients and suppliers)
     *
     * @return array
     */
    private function getEntityData()
    {
        return [
            'clients' => Client::all(),
            'suppliers' => Supplier::all(),
            'payments' => Payment::with('supplier', 'site')->get()
        ];
    }

    /**
     * Consolidate data for view
     *
     * @param array $entityData
     * @param array $payments
     * @return \Illuminate\Support\Collection
     */
    private function consolidateData($entityData)
    {
        $data = collect();
        $sum_total_payment_amount = 0;

        $data = $data->merge($entityData['suppliers']->map(function ($supplier) {
            return [
                'suppliers' => $supplier ?? 'NA',
                'category' => 'Suppliers',
            ];
        }));

        $data = $data->merge($entityData['clients']->map(function ($client) {
            return [
                'clients' => $client ?? 'NA',
                'category' => 'Clients',
            ];
        }));

        $data = $data->merge($entityData['payments']->map(function ($pay) use (&$sum_total_payment_amount) {

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

        return $data;
    }
}

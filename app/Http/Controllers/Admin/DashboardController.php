<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Client;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;

class DashboardController extends Controller
{
    public function siteDashboard()
    {

        // Use separate queries to avoid mutation
        $ongoingSitesCount = Site::where('is_on_going', 1)->count();
        $completedSitesCount = Site::where('is_on_going', 0)->count();

        $search = request('search');

        $sitesQuery = Site::query();

        if ($search) {
            $sitesQuery->where(function ($query) use ($search) {
                $query->where('site_name', 'like', '%' . $search . '%')
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $sites = $sitesQuery
            ->withSum([
                'payments as total_payments' => fn($pay) => $pay->where([
                    ['verified_by_admin', '=', 1],
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
                        ->withSum([
                            'dailyExpenses as total_daily_expenses' => fn($q) => $q->where('verified_by_admin', 1)
                        ], 'price')
                        ->withSum([
                            'labours as total_labour_cost' => function ($query) {
                                $query->whereHas('attendances', function ($att) {
                                    $att->where('is_present', 1);
                                });
                            }
                        ], 'price')
                        ->withSum([
                            'wastas as total_wasta_cost' => function ($query) {
                                $query->whereHas('attendances', function ($att) {
                                    $att->where('is_present', 1);
                                });
                            }
                        ], 'price');
                },
            ])
            ->where('is_on_going', 1)
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

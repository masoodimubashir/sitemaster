<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;

class UnverifiedSupplierPayments extends Controller
{

    public function __invoke(string $id)
    {

        $supplier = Supplier::find($id);

        $payments = $supplier->payments()
            ->with('supplier', 'site') // Eager load the supplier relationship
            ->where('verified_by_admin', 0)
            ->paginate(10);

        return view('profile.partials.Admin.PaymentSuppliers.show-unverified_supplier_payments', compact('payments', 'supplier'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class PaymentSiteController extends Controller
{
    public function __invoke(string $id) {

        $site = Site::find($id);

        $payments = $site->paymeentSuppliers()->where('verified_by_admin', 1)->paginate(10);

        return view('profile.partials.Admin.PaymentSuppliers.site-payment-supplier', compact('payments', 'site'));

    }
}

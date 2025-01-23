<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAdminPaymentController extends Controller
{
    public function index()
    {
        return view('profile.partials.Admin.Admin.payments');
    }
}

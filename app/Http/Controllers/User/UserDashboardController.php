<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;


class UserDashboardController extends Controller
{
    public function index() {

        $user = auth()->user();

        $sites = $user->sites()->paginate(10);

        return view('profile.User.Site.site', compact('sites'));
    }



}

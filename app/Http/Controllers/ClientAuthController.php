<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientAuthController extends Controller
{
    public function login()
    {
        return view('profile.partials.Client.client-login');
    }

    public function store(Request $request)
    {


        $request->validate([
            'number' => 'required|digits:10',
            'password' => 'required',
        ]);

        if (Auth::guard('clients')->attempt($request->only('number', 'password'))) {

            return redirect()->to('client/dashboard');

        }

        return back()->withErrors([
            'password' => 'The provided credentials do not match our records.',
        ]);
    }
}

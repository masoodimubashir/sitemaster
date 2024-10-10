<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index() {


        $notifications = auth()->user()->unreadnotifications()->where('notifiable_type', User::class)->get();

        return view('profile.User.user-dashboard', compact('notifications'));
    }
}

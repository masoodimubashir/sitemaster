<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarkNotificationAsReadController extends Controller
{
    public function __invoke()
    {

        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return redirect()->back();
    }
}

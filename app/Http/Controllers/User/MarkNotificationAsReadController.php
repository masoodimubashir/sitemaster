<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarkNotificationAsReadController extends Controller
{
    public function markAllNotificationAsRead()
    {

        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return redirect()->back();
    }

    public function viewAllNotifications()
    {

        $notifications = auth()->user()->notifications()
            ->whereNull('read_at')  
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view("profile.partials.Admin.Dashboard.notifications", compact("notifications"));
    }

    public function markAsRead($id)
    {

        $notification = DB::table('notifications')->where('id', $id)->first();

        if (!$notification) {
            return redirect()->back()->with('error', 'Notification Does Not Exist');
        }

        DB::table('notifications')->where('id', $id)->update(['read_at' => now()]);

        return redirect()->back();
    }
}

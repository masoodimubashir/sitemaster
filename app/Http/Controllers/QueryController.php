<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;
use App\Notifications\QueryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;


class QueryController extends Controller
{


    // QueryController.php
    public function storeSiteQuery(Request $request)
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'message' => 'required|string|max:1000',
        ]);


        try {

            $site = Site::find($validated['site_id']);

            $data = [
                'site' => $site->site_name,
                'message' => $validated['message'],
            ];

            Notification::send(
                User::where('role_name', 'admin')->get(),
                new QueryNotification($data)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Query submitted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit query. Please try again.'
            ], 500);
        }
    }

}

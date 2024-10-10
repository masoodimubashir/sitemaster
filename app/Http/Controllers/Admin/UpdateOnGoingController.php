<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class UpdateOnGoingController extends Controller
{
    public function __invoke(Request $request , string $id){

        $site = Site::findOrFail($id);

        $site->is_on_going = !$site->is_on_going;

        $site->save();

        return redirect()->back()->with('success', 'Site status updated successfully.');

    }
}

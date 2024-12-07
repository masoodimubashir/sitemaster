<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class TrashController extends Controller
{


    public function trashedSuppliers()
    {
        $suppliers = Supplier::onlyTrashed()->orderBy('name')->paginate(10);

        return view('profile.partials.Admin.Trash.trash-suppliers', compact('suppliers'));
    }



    public function trashedSites()
    {
        $sites = Site::onlyTrashed()->orderBy('site_name')->paginate(10);

        return view('profile.partials.Admin.Trash.trash-sites', compact('sites'));
    }

    public function trashedPhase() {

        $phases = Phase::onlyTrashed()->orderBy('phase_name')->paginate(10);

        return view('profile.partials.Admin.Trash.trash-phases', compact('phases'));

    }

    public function restore($model, string $id)
    {
        switch ($model) {
            case $model === 'supplier':
                Supplier::where('id', $id)->restore();
                break;

            case $model === 'site':
                Site::where('id', $id)->restore();
                break;

            case $model === 'phase':
                Phase::where('id', $id)->restore();
        }
        return redirect()->back();
    }
}

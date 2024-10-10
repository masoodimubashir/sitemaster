<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\Site;
use App\Models\Workforce;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyWagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $daily_wagers = DailyWager::latest()->paginate(10);
        return view('profile.partials.Admin.DailyWager.daily-wager', compact('daily_wagers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sites = Site::orderBy('site_name')->get();
        return view('profile.partials.Admin.DailyWager.create-daily-wager', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {
            try {

                $request->validate([
                    'price_per_day' => 'required|integer',
                    'wager_name' => 'required|string',
                    'phase_id' => 'required|exists:phases,id',
                    'supplier_id' => 'required|exists:suppliers,id'
                ]);

                DailyWager::create($request->all());

                return response()->json(['message', 'wager created...'], 201);
                
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors' => $e->validator->errors()], 422);
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $daily_wager_id = base64_decode($id);

        $daily_wager = DailyWager::with(['phase.site', 'supplier'])->find($daily_wager_id);

        return view('profile.partials.Admin.DailyWager.edit-daily-wager', compact('daily_wager'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'price_per_day' => 'required',
            'phase_id' => 'required|exists:phases,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'wager_name' => 'required|string'
        ]);

        $daily_wager = DailyWager::find($id);

        $daily_wager->update($request->all());

        return redirect()->back()->with('message', 'wager updated..');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $daily_wager_id = base64_decode($id);

        $daily_wager = DailyWager::find($daily_wager_id);

        if (!$daily_wager) {
            return redirect()->back()->with('error', 'wager not found..');
        }

        $daily_wager->delete();

        return redirect()->back()->with('message', 'wager deleted...');
    }
}

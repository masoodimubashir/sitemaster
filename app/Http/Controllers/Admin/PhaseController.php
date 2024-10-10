<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Phar;

class PhaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phases = Phase::with('site')->paginate();

        return view('profile.partials.Admin.Phase.phase', compact('phases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $sites = Site::orderBy('site_name')->get();

        return view('profile.partials.Admin.Phase.create-phase', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        if ($request->ajax()) {

            try {
                $request->validate([
                    'phase_name' => [
                        'required',
                        'string',
                        'unique:phases,phase_name,NULL,id,site_id,' . $request->site_id
                    ],
                    'site_id' => 'required|exists:sites,id'
                ]);

                Phase::create($request->all());

                return response()->json(['message', 'phase created...'], 201);
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

        $phase_id = base64_decode($id);

        $phase = Phase::find($phase_id);

        if (!$phase) {
            return redirect()->back()->with('error', 'phase not found..');
        }

        return view('profile.partials.Admin.Phase.edit-phase', compact('phase'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $phase_id = base64_decode($id);

        $request->validate([
            'phase_name' => ['required', 'string', Rule::unique(Phase::class)->ignore($phase_id)]
        ]);

        $phase = Phase::find($phase_id);

        $phase->update($request->all());

        return redirect()->route('phase.index')->with('message', 'phase updated...');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $phase_id = base64_decode($id);

        $phase = Phase::find($phase_id);

        if (!$phase) {
            return redirect()->back()->with('error', ' Something went wrong! Phase cannot be deleted...');
        }

        $phase->delete();

        return redirect()->back()->with('message', 'phase deleted');
    }
}

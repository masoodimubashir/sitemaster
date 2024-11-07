<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Phar;

class PhaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phases = Phase::paginate(10);

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


            $validator = Validator::make($request->all(), [
                'phase_name' => [
                    'required',
                    'string',
                    Rule::unique('phases')->where(function ($query) use ($request) {
                        return $query->where('site_id', $request->site_id);
                    }),
                ],
                'site_id' => 'required|exists:sites,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again'], 422);
            }

            try {
                // Create the phase after validation passes
                Phase::create($request->all());

                return response()->json(['message' => 'Phase created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred.'], 500);
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
            return redirect()->back()->with('status', 'not_found');
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

        return redirect()->route('phase.index')->with('status', 'update');
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

        return redirect()->back()->with('status', 'delete');
    }
}

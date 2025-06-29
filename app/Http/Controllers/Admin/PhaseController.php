<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PhaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phases = Phase::with('site')->paginate(10);
        $sites = Site::where('is_on_going', 1)->latest()->get();

        return view('profile.partials.Admin.Phase.phase', compact('phases', 'sites'));
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
                'site_id' => 'required|exists:sites,id',
                'phase_name' => ['required', 'string',],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                Phase::create($request->all());
                return response()->json(['message' => 'Phase created successfully.'], 201);
            } catch (\Exception $e) {
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

        $user = auth()->user();

        if (!$phase) {
            return redirect()->back()->with('status', 'not_found');
        }

        return view('profile.partials.Admin.Phase.edit-phase', compact('phase', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $phase_id = base64_decode($id);

        $phase = Phase::find($phase_id);

        $request->validate([
            'phase_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('phases')
                    ->where(function ($query) use ($phase) {
                        return $query->where('site_id', $phase->site_id);
                    })
                    ->ignore($phase_id)
            ],
        ]);

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

        $hasPaymentRecords = Payment::where(function ($query) use ($phase) {
            $query->where('site_id', $phase->site_id);
        })->exists();

        if ($hasPaymentRecords) {
            return redirect()->back()->with('status', 'data');
        }

        $phase->delete();

        return redirect()->back()->with('status', 'delete');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\Workforce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
            // Validation rules
            $validator = Validator::make($request->all(), [
                'price_per_day' => 'required|numeric|max:9999999999',
                'wager_name' => 'required|string|max:255',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again!'], 422);
            }

            try {
                // Create the daily wager
                DailyWager::create([
                    'price_per_day' => $request->price_per_day,
                    'wager_name' => $request->wager_name,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                    'verified_by_admin' => true,
                ]);

                return response()->json(['message' => 'Wager created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
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
            'price_per_day' => 'required|decimal:0,2|min:0|between:0,9999999999.99',
            'phase_id' => 'required|exists:phases,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'wager_name' => 'required|string'
        ]);

        $daily_wager = DailyWager::find($id);

        $daily_wager->update($request->all());

        return redirect()->route('sites.show', [base64_encode($daily_wager->phase->site->id)])->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {


        try {

            $daily_wager = DailyWager::find($id);


            $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($daily_wager) {
                $query->where('site_id', $daily_wager->phase->site_id)
                ->orWhere('supplier_id', $daily_wager->supplier_id);
            })->exists();

            if ($hasPaymentRecords) {
                return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
            }

            $daily_wager->delete();

            return response()->json(['message' => 'Item Deleted Successfully...'], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
        }
    }
}


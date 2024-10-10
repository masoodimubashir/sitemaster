<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\Phase;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use App\Models\Workforce;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SquareFootageBillsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $square_footage_bills =  SquareFootageBill::latest()->paginate(10);

        return view('profile.partials.Admin.SquareFootageBills.square-footage-bills', compact('square_footage_bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phases = Phase::orderBy('phase_name')->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('profile.partials.Admin.SquareFootageBills.create-square-footage-bills', compact('phases', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {


            try {
                $request->validate([
                    'image_path' => 'required|mimes:png,jpg,webp|max:1024',
                    'wager_name' => 'required|string|max:255',
                    'price' => 'required|numeric|min:0',
                    'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                    'multiplier' => 'required|numeric|min:0',
                    'phase_id' => 'required|exists:phases,id',
                    'supplier_id' => 'required|exists:suppliers,id'
                ]);

                $image_path = null;

                if ($request->hasFile('image_path')) {
                    $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
                }


                SquareFootageBill::create([
                    'image_path' => $image_path,
                    'wager_name' =>  $request->wager_name,
                    'price' => $request->price,
                    'type' => $request->type,
                    'multiplier' =>  $request->type === 'full_contract' ? 1 : $request->multiplier,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                ]);

                return response()->json(['message' => 'square footage bill created...'], 201);
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

        $square_footage_bill_id = base64_decode($id);

        $square_footage_bill = SquareFootageBill::with(['phase.site', 'supplier'])->find($square_footage_bill_id);

        return view('profile.partials.Admin.SquareFootageBills.edit-square-footage-bills', compact('square_footage_bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $square_footage_bill_id = base64_decode($id);

        $request->validate([
            'price_per_square_feet' => 'required',
            'wager_name' => 'required|string',
            'total_square_feet' => 'required',
            'site_id' => 'required|exists:sites,id'
        ]);

        $square_footage_bill = SquareFootageBill::find($square_footage_bill_id);

        $square_footage_bill->update($request->all());

        return redirect()->route('square-footage-bills.index')->with('message', 'square footage bill updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $square_footage_bill_id = base64_decode($id);

        $square_footage_bill = SquareFootageBill::find($square_footage_bill_id);

        if (!$square_footage_bill_id) {
            return redirect()->back()->with('error', 'square footage bill deleted');
        }

        $square_footage_bill->delete();

        return redirect()->back()->with('message', 'square footage deleted...');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use App\Models\PaymentSupplier;
use App\Models\Phase;
use App\Models\Site;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use App\Models\Workforce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
            // Validation rules
            $validator = Validator::make($request->all(), [
                'image_path' => 'required|mimes:png,jpg,webp,jpeg|max:1024',
                'wager_name' => 'required|string|max:255',
                'price' => 'required|numeric|max:9999999999',
                'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                'multiplier' => 'required|numeric|min:0',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again!'], 422);
            }

            $image_path = null;

            // Process the image if it exists
            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
            }

            try {
                // Create the square footage bill
                SquareFootageBill::create([
                    'image_path' => $image_path,
                    'wager_name' => $request->wager_name,
                    'price' => $request->price,
                    'type' => $request->type,
                    'multiplier' => $request->type === 'full_contract' ? 1 : $request->multiplier,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                    'verified_by_admin' => true
                ]);

                return response()->json(['message' => 'Square footage bill created successfully.'], 201);
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
            'image_path' => 'sometimes|mimes:png,jpg,webp,jpeg|max:1024',
            'wager_name' => 'required|string|max:255',
            'price' => 'required|numeric|max:9999999999',
            'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
            'multiplier' => 'required|numeric|min:0',
            'phase_id' => 'required|exists:phases,id',
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        $square_footage_bill = SquareFootageBill::find($square_footage_bill_id);

        $image_path = null;

        if ($request->hasFile('image_path')) {

            if (Storage::disk('public')->exists($square_footage_bill->image_path)) {

                Storage::disk('public')->delete($square_footage_bill->image_path);
            }

            $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
        } else {

            $image_path = $square_footage_bill->image_path;
        }

        $square_footage_bill->update([
            'image_path' => $image_path,
            'wager_name' => $request->wager_name,
            'price' => $request->price,
            'type' => $request->type,
            'multiplier' => $request->type === 'full_contract' ? 1 : $request->multiplier,
            'phase_id' => $request->phase_id,
            'supplier_id' => $request->supplier_id,
        ]);

        return redirect()->route('sites.show', [base64_encode($square_footage_bill->phase->site->id)])
            ->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {

            $square_footage_bill = SquareFootageBill::find($id);

            $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($square_footage_bill) {
                $query->where('site_id', $square_footage_bill->phase->site_id)
                    ->orWhere('supplier_id', $square_footage_bill->supplier_id);
            })->exists();

            if ($hasPaymentRecords) {
                return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
            }

            // Delete the associated image if it exists
            if ($square_footage_bill->image_path && Storage::exists($square_footage_bill->image_path)) {
                Storage::delete($square_footage_bill->image_path);
            }

            $square_footage_bill->delete();

            return response()->json(['message' => 'Item Deleted...'], 201);

        } catch (\Throwable $th) {

            return response()->json(['error' => 'An unexpected error occurred: '], 500);
        }
    }
}

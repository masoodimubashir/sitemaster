<?php

namespace App\Http\Controllers\Admin;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConstructionMaterialBilling extends Controller
{


    use HelperClass;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $construction_material_billings = ModelsConstructionMaterialBilling::latest()->paginate(10);

        return view('profile.partials.Admin.ConstructionMaterialBillings.material-billings', compact('construction_material_billings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $sites = Site::orderBy('id', 'desc')->get();

        $suppliers = Supplier::orderBy('id', 'desc')->get();

        $phases = Phase::latest()->get();

        return view(
            'profile.partials.Admin.ConstructionMaterialBillings.create-billings',
            compact(
                'sites',
                'suppliers',
                'phases'
            )
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {



            DB::beginTransaction();

            try {
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'image' => 'nullable|mimes:png,jpg,webp|max:1024',
                    'amount' => 'nullable|numeric|min:0|max:1000000',
                    'item_name' => 'nullable|string|max:255',
                    'custom_item_name' => 'nullable|string|max:255',
                    'supplier_id' => 'required|exists:suppliers,id',
                    'phase_id' => 'required|exists:phases,id',
                    'unit_count' => 'required|integer|min:1',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Handle image upload
                $image_path = null;
                if ($request->hasFile('image')) {
                    $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
                }

                $itemName = $request->input('item_name') ?? $request->input('custom_item_name');


                // Create the billing record
                $constructionBilling = ModelsConstructionMaterialBilling::create([
                    'amount' => $request->input('amount', 0.00),
                    'item_image_path' => $image_path,
                    'item_name' => $itemName,
                    'verified_by_admin' => 1,
                    'supplier_id' => $request->input('supplier_id'),
                    'user_id' => auth()->user()->id,
                    'phase_id' => $request->input('phase_id'),
                    'unit_count' => $request->input('unit_count'),
                ]);

                // Update site total amount
                $this->setSiteTotalAmount(
                    $request->phase_id,
                    (float) ($request->input('amount', 0.00))
                );

                DB::commit();

                return response()->json([
                    'message' => 'Construction bill created successfully',
                    'data' => $constructionBilling
                ], 201);

            } catch (Exception $e) {
                DB::rollBack();

                Log::error('Error creating construction billing: ' . $e->getMessage());

                return response()->json([
                    'error' => 'An unexpected error occurred. Please try again later.',
                ], 500);
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

        $construction_id = base64_decode($id);

        $construction_material_billing = ModelsConstructionMaterialBilling::with('phase.site')->find($construction_id);

        $suppliers = Supplier::where('is_raw_material_provider', 1)->orderBy('id', 'desc')->get();

        $items = Item::orderBy('item_name')->get();

        return view('profile.partials.Admin.ConstructionMaterialBillings.edit-billings', compact('construction_material_billing', 'suppliers', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        return DB::transaction(function () use ($request, $id) {
            try {
                $construction_material_billing = ModelsConstructionMaterialBilling::findOrFail($id);

                $validator = Validator::make($request->all(), [
                    'image' => 'nullable|mimes:png,jpg,webp|max:1024',
                    'amount' => 'nullable|numeric|min:0|max:1000000',
                    'item_name' => 'required_without:custom_item_name|string|max:255',
                    'custom_item_name' => 'required_without:item_name|string|max:255',
                    'supplier_id' => 'required|exists:suppliers,id',
                    'phase_id' => 'required|exists:phases,id',
                    'unit_count' => 'required|integer|min:1',
                ], [
                    'item_name.required_without' => 'This field is required',
                    'custom_item_name.required_without' => 'This field is required',
                ]);

                // Determine which item name to use
                $itemName = $request->input('item_name') ?? $request->input('custom_item_name');
                $previousAmount = $construction_material_billing->amount;
                $image_path = $construction_material_billing->item_image_path;

                // Handle image upload/update
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($image_path && Storage::disk('public')->exists($image_path)) {
                        Storage::disk('public')->delete($image_path);
                    }
                    $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
                }

                // Update billing record
                $construction_material_billing->update([
                    'amount' => $request->amount ?? 0.00,
                    'item_image_path' => $image_path,
                    'item_name' => $itemName,
                    'verified_by_admin' => 1,
                    'supplier_id' => $request->supplier_id,
                    'user_id' => auth()->id(),
                    'phase_id' => $request->phase_id,
                    'unit_count' => $request->unit_count,
                ]);

                // Update financial balances
                $amountDifference = ($request->amount ?? 0.00) - $previousAmount;
                $this->updateSiteTotalAmount($request->phase_id, $amountDifference);

                return redirect('/admin/sites/details/' . base64_encode($construction_material_billing->phase->site->id))
                    ->with('status', 'Billing updated successfully');

            } catch (Exception $exception) {
                Log::error('Error updating construction billing: ' . $exception->getMessage());
                return redirect()->back()
                    ->with('error', 'An error occurred while updating the billing record: ' . $exception->getMessage());
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            return DB::transaction(function () use ($id) {
                $construction_material_billing = ModelsConstructionMaterialBilling::findOrFail($id);

                $hasPaymentRecords = Payment::where(function ($query) use ($construction_material_billing) {
                    $query->where('site_id', $construction_material_billing->phase->site_id)
                        ->orWhere('supplier_id', $construction_material_billing->supplier_id);
                })->exists();

                if ($hasPaymentRecords) {
                    return response()->json([
                        'error' => 'This Item Cannot Be Deleted. Payment Records Exist.'
                    ], 404);
                }

                if ($construction_material_billing->item_image_path && Storage::exists($construction_material_billing->item_image_path)) {
                    Storage::delete($construction_material_billing->item_image_path);
                }

                $this->updateBalanceOnDelete($construction_material_billing->phase_id, $construction_material_billing->amount);
                $construction_material_billing->delete();

                return response()->json([
                    'message' => 'Item Deleted Successfully'
                ], 200);
            });
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
        }
    }
}

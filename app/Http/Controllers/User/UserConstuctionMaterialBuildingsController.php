<?php

namespace App\Http\Controllers\User;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;
use App\Models\Item;
use App\Models\Supplier;
use App\Notifications\VerificationNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserConstuctionMaterialBuildingsController extends Controller
{

    use HelperClass;


    public function store(Request $request)
    {
        if ($request->ajax()) {

            DB::beginTransaction();

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

            $image_path = null;

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
            }

            try {
                $material = new ModelsConstructionMaterialBilling();
                $material->amount = $request->input('amount');
                $material->item_image_path = $image_path;
                $material->item_name = $request->input('item_name');
                $material->verified_by_admin = 0;
                $material->supplier_id = $request->input('supplier_id');
                $material->user_id = auth()->user()->id;
                $material->phase_id = $request->input('phase_id');
                $material->unit_count = $request->input('unit_count');
                $material->save();

                $data = [
                    'user' => auth()->user()->name,
                    'item' => $material->item_name
                ];

                if ($material) {

                    // Update site total amount
                    $this->setSiteTotalAmount(
                        $request->phase_id,
                        (float) ($request->input('amount', 0.00))
                    );

                    DB::commit();
                }

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Construction Created...'], 201);
            } catch (Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }
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


                $construction_material_billing = ModelsConstructionMaterialBilling::findOrFail($id);
                $previousAmount = $construction_material_billing->amount;
                $image_path = $construction_material_billing->item_image_path;

                // Handle image upload/update
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if (
                        $construction_material_billing->item_image_path &&
                        Storage::disk('public')->exists($construction_material_billing->item_image_path)
                    ) {
                        Storage::disk('public')->delete($construction_material_billing->item_image_path);
                    }

                    $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
                }

                // Update billing record
                $construction_material_billing->update([
                    'amount' => $validatedData['amount'],
                    'item_image_path' => $image_path,
                    'item_name' => $validatedData['item_name'],
                    'supplier_id' => $validatedData['supplier_id'],
                    'user_id' => auth()->id(),
                    'phase_id' => $validatedData['phase_id'],
                    'unit_count' => $validatedData['unit_count']
                ]);

                // Update financial balances
                $new_amount = $this->adjustBalance($validatedData['amount'], $previousAmount);
                $this->updateSiteTotalAmount($validatedData['phase_id'], $new_amount);

                return redirect('/user/sites/details/' . ($construction_material_billing->phase->site->id))
                    ->with('status', 'update');
            } catch (Exception $exception) {
                Log::error($exception);
                return redirect('/user/sites/details/' . ($construction_material_billing->phase->site->id))
                    ->with('error', 'An error occurred while updating the billing record.');
            }
        });

    }
}

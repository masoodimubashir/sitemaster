<?php

namespace App\Http\Controllers\Admin;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:png,jpg,webp|max:1024',
                'amount' => 'required|numeric|max:1000000',
                'item_name' => 'required|string',
                'supplier_id' => 'required|exists:suppliers,id',
                'phase_id' => 'required|exists:phases,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => 'Form Fields Are Missing'
                ], 422);
            }

            $image_path = null;

            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
            }

            try {

                $constructionBilling = new ModelsConstructionMaterialBilling();
                $constructionBilling->amount = $request->input('amount');
                $constructionBilling->item_image_path = $image_path;
                $constructionBilling->item_name = $request->input('item_name');
                $constructionBilling->verified_by_admin = 1;
                $constructionBilling->supplier_id = $request->input('supplier_id');
                $constructionBilling->user_id = auth()->user()->id;
                $constructionBilling->phase_id = $request->input('phase_id');
                $constructionBilling->save();

                if ($constructionBilling) {

                    $this->setSiteTotalAmount($request->phase_id, $request->amount);

                    DB::commit();
                }

                return response()->json([
                    'message' => 'Construction bill Created'
                ], 201);
            } catch (\Exception $e) {

                DB::rollBack();

                return response()->json([
                    'error' => 'An unexpected error occurred. Please try again.',
                ], 500);
            }
        }

        return response()->json(['error' => 'Invalid request.'], 400);
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

        $construction_material_billing =  ModelsConstructionMaterialBilling::with('phase.site')->find($construction_id);

        $suppliers = Supplier::where('is_raw_material_provider', 1)->orderBy('id', 'desc')->get();

        return view('profile.partials.Admin.ConstructionMaterialBillings.edit-billings', compact('construction_material_billing', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {


        return DB::transaction(function () use ($request, $id) {
            $validatedData = $request->validate([
                'image' => 'sometimes|mimes:png,jpg,webp|max:1024',
                'amount' => 'required|numeric|max:9999999999',
                'item_name' => 'required',
                'supplier_id' => 'required|exists:suppliers,id',
                'phase_id' => 'required|exists:phases,id'
            ]);

            $construction_material_billing = ModelsConstructionMaterialBilling::find($id);

            $amount = $construction_material_billing->amount;

            $image_path = null;

            if ($request->hasFile('image')) {

                if (Storage::disk('public')->exists($construction_material_billing->item_image_path)) {

                    Storage::disk('public')->delete($construction_material_billing->item_image_path);
                }

                $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
            } else {

                $image_path = $construction_material_billing->item_image_path;
            }

            $construction_material_billing->amount = $validatedData['amount'];
            $construction_material_billing->item_image_path = $image_path;
            $construction_material_billing->item_name = $validatedData['item_name'];
            $construction_material_billing->verified_by_admin = 1;
            $construction_material_billing->supplier_id = $validatedData['supplier_id'];
            $construction_material_billing->user_id = auth()->user()->id;
            $construction_material_billing->phase_id = $validatedData['phase_id'];
            $construction_material_billing->save();

            if ($construction_material_billing) {

                $new_amount = $this->adjustBalance($request->amount, $amount);

                $this->updateSiteTotalAmount($request->phase_id, $new_amount);
            }

            return redirect()->route(
                'sites.show',
                [base64_encode($construction_material_billing->phase->site->id)]
            )
                ->with('status', 'update');
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
        }
    }
}

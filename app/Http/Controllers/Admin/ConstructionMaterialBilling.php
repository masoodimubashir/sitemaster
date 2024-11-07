<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ConstructionMaterialBilling extends Controller
{
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

        return view('profile.partials.Admin.ConstructionMaterialBillings.create-billings', compact('sites', 'suppliers', 'phases'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {


            $validator = Validator::make($request->all(), [
                'image' => 'sometimes|mimes:png,jpg,webp|max:1024',
                'amount' => 'required|numeric|max:1000000',
                'item_name' => 'required|string',
                'supplier_id' => 'required|exists:suppliers,id',
                'phase_id' => 'required|exists:phases,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again'], 422);
            }

            $image_path = null;
            if ($request->hasFile('image')) {
                $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
            }

            try {
                // Create the construction billing entry
                $constructionBilling = new ModelsConstructionMaterialBilling();
                $constructionBilling->amount = $request->input('amount');
                $constructionBilling->item_image_path = $image_path;
                $constructionBilling->item_name = $request->input('item_name');
                $constructionBilling->verified_by_admin = 1; // or set based on logic
                $constructionBilling->supplier_id = $request->input('supplier_id');
                $constructionBilling->user_id = auth()->user()->id; // Ensure user is authenticated
                $constructionBilling->phase_id = $request->input('phase_id');
                $constructionBilling->save();

                return response()->json(['message' => 'Construction billing created successfully'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
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

        $validatedData = $request->validate([
            'image' => 'sometimes|mimes:png,jpg,webp|max:1024',
            'amount' => 'required|numeric|max:9999999999',
            'item_name' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'phase_id' => 'required|exists:phases,id'
        ]);

        $construction_material_billing = ModelsConstructionMaterialBilling::find($id);

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

        return redirect()->route('sites.show', [base64_encode($construction_material_billing->phase->site->id)])
            ->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $construction_id = base64_decode($id);

        $construction_material_billing = ModelsConstructionMaterialBilling::find($construction_id);

        Storage::delete($construction_material_billing->item_image_path);

        $construction_material_billing->delete();

        return redirect()->route('construction-material-billings.index')->with('message', 'data deleted succussfully');
    }
}

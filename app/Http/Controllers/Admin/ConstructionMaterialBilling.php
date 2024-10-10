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



            try {
                // Validate the incoming request data
                $validatedData = $request->validate([
                    'image' => 'sometimes|mimes:png,jpg,webp|max:1024',
                    'amount' => 'required',
                    'item_name' => 'required',
                    'supplier_id' => 'required|exists:suppliers,id',
                    'phase_id' => 'required|exists:phases,id'
                ]);

                $image_path = null;

                if ($request->hasFile('image')) {

                    $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
                }


                $constructionBilling = new ModelsConstructionMaterialBilling();
                $constructionBilling->amount = $validatedData['amount'];
                $constructionBilling->item_image_path = $image_path;
                $constructionBilling->item_name = $validatedData['item_name'];
                $constructionBilling->verified_by_admin = 1;
                $constructionBilling->supplier_id = $validatedData['supplier_id'];
                $constructionBilling->user_id = auth()->user()->id;
                $constructionBilling->phase_id = $validatedData['phase_id'];
                $constructionBilling->save();

                return response()->json(['message' => 'Construction billing created successfully'], 201);
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
            'amount' => 'required',
            'item_name' => 'required',
            // 'site_id' => 'required|exists:sites,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'phase_id' => 'required|exists:phases,id'
        ]);

        $construction_material_billing = ModelsConstructionMaterialBilling::find($id);

        $image_path = null;

        if ($request->hasFile('image')) {

            if (Storage::exists($construction_material_billing->item_image_path)) {

                Storage::delete($construction_material_billing->item_image_path);
            }

            $image_path = $request->file('image')->store('ConstructionBillingImage', 'public');
        } else {

            $image_path = $construction_material_billing->item_image_path;
        }

        $construction_material_billing->amount = $validatedData['amount'];
        $construction_material_billing->item_image_path = $image_path;
        $construction_material_billing->item_name = $validatedData['item_name'];
        $construction_material_billing->verified_by_admin = 1;
        // $construction_material_billing->site_id = $validatedData['site_id'];
        $construction_material_billing->supplier_id = $validatedData['supplier_id'];
        $construction_material_billing->user_id = auth()->user()->id;
        $construction_material_billing->phase_id = $validatedData['phase_id'];
        $construction_material_billing->save();

        return redirect()->back()
            ->with('message', 'Construction billing updated successfully');
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
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Site;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;

use Illuminate\Http\Request;

class UserConstuctionMaterialBuildingsController extends Controller
{

    public function __invoke(Request $request)
    {


        $validatedData = $request->validate([
            'image' => 'required|mimes:png,jpg,webp|max:1024',
            'amount' => 'required',
            'item_name' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'phase_id' => 'required|exists:phases,id'
        ]);

        $image_path = null;

        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('ConstructionBillingImage');
        }

        $constructionBilling = new ModelsConstructionMaterialBilling();
        $constructionBilling->amount = $validatedData['amount'];
        $constructionBilling->item_image_path = $image_path;
        $constructionBilling->item_name = $validatedData['item_name'];
        $constructionBilling->verified_by_admin = 1;
        $constructionBilling->supplier_id = $validatedData['supplier_id'];
        $constructionBilling->supplier_id = $validatedData['supplier_id'];
        $constructionBilling->user_id = auth()->user()->id;
        $constructionBilling->phase_id = $validatedData['phase_id'];
        $constructionBilling->save();

        return redirect()->back()
            ->with('message', 'Construction billing created successfully');
    }
}

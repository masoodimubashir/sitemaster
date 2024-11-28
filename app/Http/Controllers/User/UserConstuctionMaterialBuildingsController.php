<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ConstructionMaterialBilling as ModelsConstructionMaterialBilling;
use App\Notifications\VerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UserConstuctionMaterialBuildingsController extends Controller
{


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
                return response()->json(['errors' => 'Form Fields Are Missing'], 422);
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
                $material->save();

                $data = [
                    'user' => auth()->user()->name,
                    'item' => $material->item_name
                ];

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Construction billing created successfully'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }
    }
}



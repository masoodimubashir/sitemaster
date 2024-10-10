<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SquareFootageBill;
use Illuminate\Http\Request;

class UserSquareFootageBillsController extends Controller
{
    public function __invoke(Request $request)
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
}

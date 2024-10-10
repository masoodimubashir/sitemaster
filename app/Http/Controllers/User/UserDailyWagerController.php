<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyWager;
use Illuminate\Http\Request;

class UserDailyWagerController extends Controller
{
    public function __invoke(Request $request)
    {
        if ($request->ajax()) {
            try {

                $request->validate([
                    'price_per_day' => 'required|integer',
                    'wager_name' => 'required|string',
                    'phase_id' => 'required|exists:phases,id',
                    'supplier_id' => 'required|exists:suppliers,id'
                ]);

                DailyWager::create($request->all());

                return response()->json(['message', 'wager created...'], 201);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json(['errors' => $e->validator->errors()], 422);
            }
        }
    }
}

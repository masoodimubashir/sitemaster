<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyExpenses;
use Illuminate\Http\Request;

class UserDailyExpensesController extends Controller
{
    public function __invoke(Request $request) {

        $request->validate([
            'item_name' => 'required|string',
            'price' => 'required',
            'phase_id' => 'required|exists:phases,id',
        ]);

        DailyExpenses::create([
            'item_name' => $request->item_name,
            'price' => $request->price,
            'phase_id' => $request->phase_id,
            'user_id' => auth()->user()->id
        ]);

        return redirect()->back()->with('message', 'expenses detail created..');
    }
}

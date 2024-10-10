<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyExpenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DailyExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if ($request->ajax()) {

            try {

                $request->validate([
                    'item_name' => 'required|string',
                    'price' => 'required',
                    'bill_photo' => 'required|image|mimes:jpg,jpeg,webp|max:1024',
                    'phase_id' => 'required|exists:phases,id',
                ]);


                $image_path = null;

                if ($request->hasFile('bill_photo')) {

                    $image_path = $request->file('bill_photo')->store('Expenses', 'public');
                }

                DailyExpenses::create([
                    'bill_photo' => $image_path,
                    'item_name' => $request->item_name,
                    'price' => $request->price,
                    'phase_id' => $request->phase_id,
                    'user_id' => auth()->user()->id
                ]);

                return response()->json(['message', 'expenses detail created..'], 201);

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
        $expense_id = base64_decode($id);

        $dialy_expense = DailyExpenses::with('phase.site')->find($expense_id);

        return view('profile.partials.Admin.DailyExpenses.edit-daily-expense', compact('dialy_expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'item_name' => 'required|string',
            'bill_photo' => 'required|image|mimes:jpg,jpeg,webp|max:1024',
            'price' => 'required',
            'phase_id' => 'required|exists:phases,id',
        ]);

        $daily_expense = DailyExpenses::findorFail($id);


        $image_path = null;

        if ($request->hasFile('bill_photo')) {

            if (Storage::exists($daily_expense->item_image_path)) {

                Storage::delete($daily_expense->item_image_path);
            }

            $image_path = $request->file('bill_photo')->store('Expenses', 'public');
        } else {

            $image_path = $daily_expense->bill_photo;
        }

        $daily_expense->update([
            'item_name' => $request->item_name,
            'bill_photo' => $image_path,
            'price' => $request->price,
            'phase_id' => $request->phase_id,
            'user_id' => auth()->user()->id
        ]);

        return redirect()->back()->with('message', 'expenses detail updated..');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\DailyExpenses;
use App\Models\PaymentSupplier;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DailyExpensesController extends Controller
{


    use HelperClass;

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


            DB::beginTransaction();

            // Validation rules
            $validator = Validator::make($request->all(), [
                'item_name' => 'required|string',
                'price' => 'required|numeric|max:9999999999',
                'bill_photo' => 'required|image|mimes:jpg,jpeg,webp,png|max:1024',
                'phase_id' => 'required|exists:phases,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => 'Validation Error.. Try Again!'], 422);
            }

            $image_path = null;

            // Process the image if it exists
            if ($request->hasFile('bill_photo')) {
                $image_path = $request->file('bill_photo')->store('Expenses', 'public');
            }

            try {
                // Create the daily expenses entry
                $expense = DailyExpenses::create([
                    'bill_photo' => $image_path,
                    'item_name' => $request->item_name,
                    'price' => $request->price,
                    'phase_id' => $request->phase_id,
                    'user_id' => auth()->user()->id,
                    'verified_by_admin' => true,
                ]);

                if ($expense) {

                    $this->setSiteTotalAmount($request->phase_id, $request->price);

                    DB::commit();
                }

                return response()->json(['message' => 'Expenses detail created successfully.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
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


        return DB::transaction(function () use ($request, $id) {

            $request->validate([
                'item_name' => 'required|string|max:255',
                'price' => 'required|numeric|max:9999999999',
                'bill_photo' => 'sometimes|mimes:jpg,jpeg,webp|max:1024',
                'phase_id' => 'required|exists:phases,id',
            ]);

            $daily_expense = DailyExpenses::findorFail($id);

            $image_path = null;
            $old_amount = $daily_expense->price;


            if ($request->hasFile('bill_photo')) {

                if (Storage::disk('public')->exists($daily_expense->bill_photo)) {

                    Storage::disk('public')->delete($daily_expense->bill_photo);
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

            if ($daily_expense) {

                $new_amount = $this->adjustBalance($request->price, $old_amount);


                $this->updateSiteTotalAmount($request->phase_id, $new_amount);
            }

            return redirect()->route(
                'sites.show',
                [base64_encode($daily_expense->phase->site->id)]
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

            $expense = DailyExpenses::find($id);

            $hasPaymentRecords = PaymentSupplier::where(function ($query) use ($expense) {
                $query->where('site_id', $expense->phase->site_id);
            })->exists();

            if ($hasPaymentRecords) {
                return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
            }

            // Delete the associated image if it exists
            if ($expense->bill_photo && Storage::exists($expense->bill_photo)) {
                Storage::delete($expense->bill_photo);
            }

            $expense->delete();

            return response()->json(['message' => 'Item Deleted Successfully'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\DailyExpenses;
use App\Models\Payment;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
                'item_name' => 'nullable|string',
                'price' => 'required|numeric|max:9999999999',
                'bill_photo' => 'nullable|image|mimes:jpg,jpeg,webp,png|max:1024',
                'phase_id' => 'required|exists:phases,id',
                'site_id' => 'required|exists:sites,id',
            ]);

            // Check for validation errors
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
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
                    'site_id' => $request->site_id,
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
            try {
                $validatedData = $request->validate([
                    'item_name' => 'required|string|max:255',
                    'price' => 'required|numeric|max:9999999999',
                    'bill_photo' => 'nullable|image|mimes:jpg,jpeg,webp,png|max:1024',
                    'phase_id' => 'required|exists:phases,id',
                ]);

                $daily_expense = DailyExpenses::findOrFail($id);
                $old_amount = $daily_expense->price;
                $image_path = $daily_expense->bill_photo;

                // Handle file upload
                if ($request->hasFile('bill_photo')) {
                    // Delete old image if exists
                    if ($image_path && Storage::disk('public')->exists($image_path)) {
                        Storage::disk('public')->delete($image_path);
                    }
                    $image_path = $request->file('bill_photo')->store('Expenses', 'public');
                }

                // Update record
                $daily_expense->update([
                    'item_name' => $validatedData['item_name'],
                    'bill_photo' => $image_path,
                    'price' => $validatedData['price'],
                    'phase_id' => $validatedData['phase_id'],
                    'user_id' => auth()->id()
                ]);

                // Update balances
                $new_amount = $this->adjustBalance($validatedData['price'], $old_amount);
                $this->updateSiteTotalAmount($validatedData['phase_id'], $new_amount);

                return redirect('/admin/sites/details/' . base64_encode($daily_expense->phase->site->id))
                    ->with('status', 'update');

            } catch (ModelNotFoundException $e) {
                return back()->with('error', 'Expense record not found');
            } catch (ValidationException $e) {
                return back()->withErrors($e->validator)->withInput();
            } catch (Exception $e) {
                Log::error('Daily expense update error: ' . $e->getMessage());
                return back()->with('error', 'An error occurred while updating the expense: ' . $e->getMessage());
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        return DB::transaction(function () use ($id) {


            try {

                $expense = DailyExpenses::find($id);

                $hasPaymentRecords = Payment::where(function ($query) use ($expense) {
                    $query->where('site_id', $expense->phase->site_id);
                })->exists();

                if ($hasPaymentRecords) {
                    return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
                }

                if ($expense->bill_photo && Storage::exists($expense->bill_photo)) {
                    Storage::delete($expense->bill_photo);
                }

                $this->updateBalanceOnDelete($expense->phase_id, $expense->price);

                $expense->delete();

                return response()->json(['message' => 'Item Deleted Successfully'], 200);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json(['error' => 'Something Went Wrong Try Again'], 500);
            }
        });
    }
}

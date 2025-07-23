<?php

namespace App\Http\Controllers\Admin;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Phase;
use App\Models\SquareFootageBill;
use App\Models\Supplier;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SquareFootageBillsController extends Controller
{


    use HelperClass;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $square_footage_bills = SquareFootageBill::latest()->paginate(10);

        return view('profile.partials.Admin.SquareFootageBills.square-footage-bills', compact('square_footage_bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $phases = Phase::orderBy('phase_name')->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('profile.partials.Admin.SquareFootageBills.create-square-footage-bills', compact('phases', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {


        DB::beginTransaction();

        if ($request->ajax()) {

            $validator = Validator::make($request->all(), [
                'image_path' => 'nullable|mimes:png,jpg,webp,jpeg|max:1024',
                'wager_name' => 'required|string|max:255',
                'price' => 'nullable|numeric|max:9999999999',
                'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                'multiplier' => 'required|numeric|min:0',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
            ], [
                'wager_name.required' => 'Work type is required.',
                'wager_name.string' => 'The work type  must be a valid string.',
                'wager_name.max' => 'The work type may not be more than 255 characters.',
            ]);

            if ($validator->fails()) {
                Log::error($validator->errors());
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            $image_path = null;

            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
            }

            $price = 0;

            $price = $request->type === 'full_contract' ? $request->price : $request->price * $request->multiplier;

            try {

                $sqft = SquareFootageBill::create([
                    'image_path' => $image_path,
                    'wager_name' => $request->wager_name,
                    'price' => $request->price,
                    'type' => $request->type,
                    'multiplier' => $request->type === 'full_contract' ? 1 : $request->multiplier,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                    'verified_by_admin' => true
                ]);

                if ($sqft) {

                    $this->setSiteTotalAmount(
                        $request->phase_id,
                        $price ?? 0.00
                    );

                    DB::commit();
                }

                return response()->json([
                    'message' => 'Square footage bill created successfully.'
                ], 201);
            } catch (\Exception $e) {

                DB::rollBack();
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
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

        $square_footage_bill_id = base64_decode($id);

        $square_footage_bill = SquareFootageBill::with(['phase.site', 'supplier'])->find($square_footage_bill_id);

        return view('profile.partials.Admin.SquareFootageBills.edit-square-footage-bills', compact('square_footage_bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {


            try {
                $square_footage_bill_id = base64_decode($id);
                if (!$square_footage_bill_id) {
                    throw new Exception('Invalid bill ID');
                }

                $validatedData = $request->validate([
                    'image_path' => 'nullable|mimes:png,jpg,webp,jpeg|max:1024',
                    'wager_name' => 'required|string|max:255',
                    'price' => 'required|numeric|max:9999999999',
                    'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                    'multiplier' => 'required|numeric|min:0',
                    'phase_id' => 'required|exists:phases,id',
                    'supplier_id' => 'required|exists:suppliers,id',
                ], [
                    'wager_name.required' => 'Work type is required.',
                    'wager_name.string' => 'The work type must be a valid string.',
                    'wager_name.max' => 'The work type may not be more than 255 characters.',
                ]);

                $square_footage_bill = SquareFootageBill::findOrFail($square_footage_bill_id);
                $phase_id = $validatedData['phase_id'];

                // Calculate amounts
                $old_amount = $square_footage_bill->price * $square_footage_bill->multiplier;
                $new_amount = $validatedData['price'] * $validatedData['multiplier'];

                // Handle image upload
                $image_path = $square_footage_bill->image_path;
                if ($request->hasFile('image_path')) {
                    // Delete old image if exists
                    if ($image_path && Storage::disk('public')->exists($image_path)) {
                        Storage::disk('public')->delete($image_path);
                    }
                    $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
                }

                // Update record
                $square_footage_bill->update([
                    'image_path' => $image_path,
                    'wager_name' => $validatedData['wager_name'],
                    'price' => $validatedData['price'],
                    'type' => $validatedData['type'],
                    'multiplier' => $validatedData['type'] === 'full_contract' ? 1 : $validatedData['multiplier'],
                    'phase_id' => $phase_id,
                    'supplier_id' => $validatedData['supplier_id'],
                ]);

                // Update balances
                $adjusted_amount = $this->adjustBalance($new_amount, $old_amount);
                $this->updateSiteTotalAmount($phase_id, $adjusted_amount);

                return redirect('/admin/sites/details/' . base64_encode($square_footage_bill->phase->site->id))
                    ->with('status', 'update');

            } catch (ModelNotFoundException $e) {
                return back()->with('error', 'Bill not found');
            } catch (ValidationException $e) {
                return back()->withErrors($e->validator)->withInput();
            } catch (Exception $e) {
                Log::error('Square footage bill update error: ' . $e->getMessage());
                return back()->with('error', 'An error occurred while updating the square footage bill: ' . $e->getMessage());
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

                $square_footage_bill = SquareFootageBill::find($id);

                $hasPaymentRecords = Payment::where(function ($query) use ($square_footage_bill) {
                    $query->where('site_id', $square_footage_bill->phase->site_id)
                        ->orWhere('supplier_id', $square_footage_bill->supplier_id);
                })->exists();

                if ($hasPaymentRecords) {
                    return response()->json(['error' => 'This Item Cannot Be Deleted. Payment records exist.'], 404);
                }

                if ($square_footage_bill->image_path && Storage::exists($square_footage_bill->image_path)) {
                    Storage::delete($square_footage_bill->image_path);
                }

                $amount = $square_footage_bill->price * $square_footage_bill->multiplier;

                $this->updateBalanceOnDelete($square_footage_bill->phase_id, $amount);

                $square_footage_bill->delete();

                return response()->json(['message' => 'Item Deleted...'], 201);
            } catch (\Throwable $th) {

                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        });
    }
}

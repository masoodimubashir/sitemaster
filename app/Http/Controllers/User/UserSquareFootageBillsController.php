<?php

namespace App\Http\Controllers\User;

use App\Class\HelperClass;
use App\Http\Controllers\Controller;
use App\Models\SquareFootageBill;
use App\Models\User;
use App\Notifications\VerificationNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;


class UserSquareFootageBillsController extends Controller
{

    use HelperClass;

    public function store(Request $request)
    {

        if ($request->ajax()) {


            DB::beginTransaction();

            // Validation rules
            $validator = Validator::make($request->all(), [
                'image_path' => 'nullable|mimes:png,jpg,webp,jpeg|max:1024',
                'wager_name' => 'required|string|max:255',
                'price' => 'required|numeric|max:9999999999',
                'type' => 'required|in:per_sqr_ft,per_unit,full_contract',
                'multiplier' => 'required|numeric|min:0',
                'phase_id' => 'required|exists:phases,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'created_at' => 'required|date',
            ], [
                'wager_name.required' => 'Work type is required.',
                'wager_name.string' => 'The work type  must be a valid string.',
                'wager_name.max' => 'The work type may not be more than 255 characters.',
            ]);


            // Check for validation errors
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $image_path = null;

            // Process the image if it exists
            if ($request->hasFile('image_path')) {
                $image_path = $request->file('image_path')->store('SquareFootageImages', 'public');
            }

            $price = 0;

            $price = $request->type === 'full_contract' ? $request->price : $request->price * $request->multiplier;

            try {
                // Create the square footage bill
                $sqft = SquareFootageBill::create([
                    'image_path' => $image_path,
                    'wager_name' => $request->wager_name,
                    'price' => $request->price,
                    'type' => $request->type,
                    'multiplier' => $request->type === 'full_contract' ? 1 : $request->multiplier,
                    'phase_id' => $request->phase_id,
                    'supplier_id' => $request->supplier_id,
                    'verified_by_admin' => 0,
                    'created_at' => $request->created_at,
                ]);

                if ($sqft) {

                    $this->setSiteTotalAmount(
                        $request->phase_id,
                        $price ?? 0.00
                    );

                    DB::commit();
                }

                $data = [
                    'user' => auth()->user()->name,
                    'item' => $sqft->wager_name
                ];

                Notification::send(
                    User::where('role_name', 'admin')->get(),
                    new VerificationNotification($data)
                );

                return response()->json(['message' => 'Square footage created.'], 201);
            } catch (\Exception $e) {
                // Handle any unexpected errors
                return response()->json(['error' => 'An unexpected error occurred: '], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
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

                return redirect('/user/sites/details/' . ($square_footage_bill->phase->site->id))
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
}

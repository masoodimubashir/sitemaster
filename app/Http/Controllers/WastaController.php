<?php

namespace App\Http\Controllers;

use App\Models\Wasta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WastaController extends Controller
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

                $validator = Validator::make($request->all(), [
                    'wager_name' => 'required|string|max:255',
                    'price_per_day' => 'required|numeric',
                    'contact' => 'required|string|max:10',
                    'phase_id' => 'required|exists:phases,id',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                Wasta::create([
                    'phase_id' => $request->phase_id,
                    'wasta_name' => $request->wager_name,
                    'price' => $request->price_per_day,
                    'contact_no' => $request->contact,
                ]);

                return response()->json([
                    'message' => 'Wasta created successfully'
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

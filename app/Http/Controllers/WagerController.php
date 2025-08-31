<?php

namespace App\Http\Controllers;

use App\Models\Wager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WagerController extends Controller
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
                    'wager_name' => 'required|array|min:1',
                    'wager_name.*' => 'required|string|max:255',
                    'wasta_id' => 'nullable|exists:wastas,id'
                ], [
                    'wager_name.required' => 'Please enter at least one wager name.',
                    'wager_name.*.required' => 'Wager name cannot be empty.',
                    'wasta_id.exists' => 'The selected wasta is invalid.'
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                    ], 422);
                }

                $validated = $validator->validated();

                foreach ($validated['wager_name'] as $name) {
                    Wager::create([
                        'wager_name' => $name,
                        'wasta_id' => $validated['wasta_id'] ?? null,
                    ]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Wager(s) created successfully!',
                ], 201);

            } catch (\Exception $e) {

                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong while saving wagers.',
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

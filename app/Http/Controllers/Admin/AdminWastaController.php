<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wasta;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminWastaController extends Controller
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
                    'wasta_name' => 'required|string|min:3',
                    'price' => 'required|integer|min:1',
                ]);

                Wasta::create($request->all());

                return response()->json([
                    'status' => 'success',
                    'message' => 'Wasta Added Successfully'
                ], 200);
            } catch (ValidationException $e) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            } catch (Exception $e) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred',
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

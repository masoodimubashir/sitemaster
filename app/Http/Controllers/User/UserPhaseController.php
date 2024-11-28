<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserPhaseController extends Controller
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
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        if ($request->ajax()) {

        Log::info('User', $request->all());


            $validator = Validator::make($request->all(), [
                'site_id' => 'required|exists:sites,id',
                'phase_name' => [
                    'required',
                    'string',
                    Rule::unique('phases', 'phase_name')
                    ->where(function ($query) use ($request) {
                        return $query->where('site_id', $request->site_id);
                    })
                ],
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => 'Phase Already Exists',], 422);
            }

            try {

                Phase::create($request->all());
                return response()->json(['message' => 'Phase created successfully.'], 201);
            } catch (\Exception $e) {

                return response()->json(['error' => 'An unexpected error occurred.'], 500);
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

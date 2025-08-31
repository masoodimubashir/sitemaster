<?php

namespace App\Http\Controllers;

use App\Models\Wasta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


// TODO: To Be Reimplemented

class WastaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $wastas = Wasta::latest()->paginate(10);
            return view('profile.partials.Admin.Wasta.wasta', compact('wastas'));

        } catch (\Exception $e) {
            return redirect()->back()->with('status', 'error')
                ->with('error_message', $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used since we use modal forms
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {
            try {
                $validator = Validator::make($request->all(), [
                    'wasta_name' => 'required|string|max:255',
                    'contact_no' => 'nullable|string|max:15',
                    'price' => 'required|integer|min:1'
                ], [
                    'wasta_name.required' => 'Wasta Name is required.',
                    'contact_no.max' => 'Contact number cannot be longer than 15 characters.',
                    'price' => 'Price is required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $wasta = Wasta::create($validator->validated());

                return response()->json([
                    'status' => true,
                    'message' => 'Wasta created successfully',
                    'data' => $wasta
                ], 201);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong while creating.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        abort(400, 'Invalid request');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $wasta = Wasta::findOrFail($id);

            return view('profile.partials.Admin.Wasta.edit-wasta', compact('wasta'));

        } catch (\Exception $e) {
            return redirect()->back()->with([
                'status' => 'error',
                'message' => 'Failed to fetch Wasta details.',
                'error' => $e->getMessage()
            ]);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'wasta_name' => 'required|string|max:255',
            'contact_no' => 'nullable|string|max:15',
            'price' => 'required|integer|min:1',
        ]);

        try {
            $wasta = Wasta::findOrFail($id);
            $wasta->update($request->all());

            return redirect()->to('/admin/wasta')->with([
                'status' => 'update',
                'message' => 'Wasta updated successfully!'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with([
                'status' => 'error',
                'message' => 'Wasta not found.',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $wasta = Wasta::findOrFail($id);
            $wasta->delete();

            // For normal delete (non-AJAX), redirect with flash message
            if (!request()->ajax()) {
                return redirect()->route('wasta.index')->with('status', 'delete');
            }

            return response()->json([
                'status' => true,
                'message' => 'Wasta deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Wasta not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while deleting.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

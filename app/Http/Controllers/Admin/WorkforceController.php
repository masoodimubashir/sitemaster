<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Workforce;
use Illuminate\Validation\Rule;

class WorkforceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workforces = Workforce::latest()->get();

        return view('profile.partials.Admin.Workforce.workforces', compact('workforces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('profile.partials.Admin.Workforce.create-workforce');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'workforce_name' => 'required|unique:workforces,workforce_name|string'
        ]);

        Workforce::create($request->all());

        return redirect()->route('workforce.index')->with('message', 'workforce created...');
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
        $workforce_id = base64_decode($id);

        $workforce = Workforce::find($workforce_id);

        if (!$workforce) {
            return redirect()->back()->with('error', 'workforce not found ! try again...');
        }

        return view('profile.partials.Admin.Workforce.edit-workforce', compact('workforce'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $workforce_id = base64_decode($id);

        $request->validate([
            'workforce_name' => ['required', 'string', Rule::unique('workforces')->ignore($workforce_id)]
        ]);

        $workforce = Workforce::find($workforce_id);

        if (!$workforce) {
            return redirect()->back()->with('error', 'workforce cannot be edited! something went wrong');
        }

        $workforce->update($request->all());

        return redirect()->route('workforce.index')->with('message', 'workforce updated..');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workforce_id = base64_decode($id);

        $workforce = Workforce::findorFail($workforce_id);

        $workforce->delete();

        return redirect()->back()->with('message', 'workforce deleted...');
    }
}

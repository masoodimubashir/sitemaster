<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::latest()->paginate(10);

        return view('profile.partials.Admin.Clients.clients', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $sites = Site::orderBy('site_name')->get();

        return view('profile.partials.Admin.Clients.create-client', compact('sites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|digits:10|unique:' . Client::class,
            'password' => [
                Password::min(6)->mixedCase()->required()
            ],
        ]);

        Client::create([
            'name' => $request->name,
            'number' => $request->number,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('clients.index')->with('message', 'client');
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
        $client_id = base64_decode($id);

        $client = Client::findOrFail($client_id);

        return view('profile.partials.Admin.Clients.edit-client', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'required|string|min:5',
            'password' => 'sometimes|min:6'
        ]);

        $client = Client::find($id);

        if (!$client) {
            return redirect()->back()->with('error', 'Client not found');
        }

        $client->update([
            'name' => $request->name,
            'password' => $request->password ?  $request->password : Hash::make($request->password)
        ]);

        $client->sites()->update(['site_name' => $request->name]);

        return redirect()->back()->with('status', 'client');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::find($id);

        if (!$client) {

            return redirect()->back()->with('error', 'Client Does Not Exist..');
        }

        $client->delete();

        return redirect()->back()->with('message', 'client has been deleted...');
    }
}

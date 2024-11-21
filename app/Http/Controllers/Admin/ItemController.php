<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConstructionMaterialBilling;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::latest()->paginate(10);

        return view('profile.partials.Admin.Item.items', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('profile.partials.Admin.Item.create-item');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|unique:items,item_name'
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')->with('status', 'create');

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
        $item = Item::find($id);

        if (!$item) {
            return redirect()->route('items.index')->with('status', 'error');
        }

        return view('profile.partials.Admin.Item.edit-item', compact( 'item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return redirect()->route('items.index')->with('status', 'error');
        }

        $request->validate([
            'item_name' => ['required', 'string', Rule::unique('items')->ignore($id)]
        ]);

        $item->update([
            'item_name' => $request->item_name
        ]);

        return redirect()->route('items.index')->with('status', 'update');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::find($id);

        if (!$item) {
            return redirect()->back()->with('status', 'error');
        }

        $hasData = ConstructionMaterialBilling::firstWhere('item_name', $item->item_name);


        if ($hasData) {
            return redirect()->back()->with('status', 'null');

        }

        $item->delete();

        return redirect()->back()->with('status', 'delete');
    }
}

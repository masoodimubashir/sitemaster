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

        $validatedData = $request->validate([
            'items' => 'required|array',
            'items.*.item_name' => 'required|string|max:255'
        ], [
            'items.*.item_name.required' => 'The item name field is required.',
            'items.*.item_name.max' => 'The item name must not exceed 255 characters.'
        ]);

        $itemsToCreate = [];
        foreach ($validatedData['items'] as $itemData) {
            $itemsToCreate[] = [
                'item_name' => $itemData['item_name'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Bulk insert
        Item::insert($itemsToCreate);

        if (auth()->user()->role_name === 'admin') {
            return redirect()->to('admin/items')->with('status', 'create');
        } else {
            return redirect()->to('user/items')->with('status', 'create');
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
        $item = Item::find($id);

        if (!$item) {
            return redirect()->route('items.index')->with('status', 'error');
        }


        return view('profile.partials.Admin.Item.edit-item', compact('item'));
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

        if (auth()->user()->role_name === 'admin') {
            return redirect()->to('admin/items')->with('status', 'update');
        } else {
            return redirect()->to('user/items')->with('status', 'update');
        }
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

        $hasItem = ConstructionMaterialBilling::where('item_name', $item->item_name)->exists();

        if ($hasItem) {
            return redirect()->back()->with('status', 'hasItem');
        }

        $item->delete();

        if (auth()->user()->role_name === 'admin') {
            return redirect()->to('admin/items')->with('status', 'delete');
        } else {
            return redirect()->to('user/items')->with('status', 'delete');
        }
    }
}

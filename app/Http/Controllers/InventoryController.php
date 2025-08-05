<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
        }

        $items = $query->get();

        return view('inventory.index', compact('items'));
    }

    public function create()
    {
        return view('inventory.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'location' => 'required|string|max:255',
        ]);

        $item = Inventory::create($validated);

        // Log creation (no item name in action)
        AuditLog::create([
            'inventory_id' => $item->id,
            'action' => 'created',
            'user' => 'admin', // replace with auth()->user()->name if using auth
            'location' => $validated['location'],
        ]);

        return redirect()->route('inventory.index')->with('success', 'Item created successfully.');
    }

    public function show(int $id)
    {
        $item = Inventory::findOrFail($id);
        return view('inventory.show', compact('item'));
    }

    public function edit(int $id)
    {
        $item = Inventory::findOrFail($id);
        return view('inventory.edit', compact('item'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'price' => 'required|numeric',
            'location' => 'required|string|max:255',
        ]);

        $item = Inventory::findOrFail($id);
        $original = $item->getOriginal();

        $item->update($validated);

        $changes = [];

        foreach ($validated as $field => $newValue) {
            $oldValue = $original[$field] ?? null;
            if ($oldValue != $newValue) {
                $changes[] = "{$field} changed from '{$oldValue}' to '{$newValue}'";
            }
        }

        if (!empty($changes)) {
            AuditLog::create([
                'inventory_id' => $item->id,
                'action' => implode('; ', $changes),
                'user' => 'admin', // replace with auth()->user()->name if using auth
                'location' => $validated['location'],
            ]);
        }

        return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
    }

    public function destroy(int $id)
    {
        $item = Inventory::findOrFail($id);

        // Simplified deletion message
        AuditLog::create([
            'inventory_id' => $item->id,
            'action' => 'deleted',
            'user' => 'admin',
            'location' => $item->location,
        ]);

        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Item deleted successfully.');
    }
}

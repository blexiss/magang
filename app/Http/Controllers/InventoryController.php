<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
public function index(Request $request)
{
    $search = trim($request->input('search'));

    try {
        // Always cache the full inventory for offline use
        $fullInventory = Inventory::all();
        Storage::put('inventory_cache.json', $fullInventory->toJson());

        // Build the query
        $query = Inventory::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('id', $search);
                }
                $q->orWhere('name', 'like', "%{$search}%");
            });
        }

        $items = $query->get();

        if ($request->ajax()) {
            return response()->json([
                'items' => $items,
            ]);
        }

        return view('inventory.index', ['items' => $items]);

    } catch (\Exception $e) {
        Log::error('DB connection failed: ' . $e->getMessage());

        if (Storage::exists('inventory_cache.json')) {
            $cachedData = json_decode(Storage::get('inventory_cache.json'));
            $items = collect($cachedData);

            if ($request->ajax()) {
                return response()->json([
                    'items' => $items,
                    'offline' => true,
                ]);
            }

            return view('inventory.index', ['items' => $items, 'offline' => true]);
        }

        return back()->withErrors('Unable to load inventory. No offline data available.');
    }
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

    if ($request->expectsJson()) {
        return response()->json(['success' => true]);
    }

    return redirect()->route('inventory.index')->with('success', 'Item updated successfully.');
}

public function destroy(int $id, Request $request)
{
    $item = Inventory::findOrFail($id);

    AuditLog::create([
        'inventory_id' => $item->id,
        'action' => 'deleted',
        'user' => 'admin',
        'location' => $item->location,
    ]);

    $item->delete();

    if ($request->expectsJson()) {
        return response()->json(['success' => true]);
    }

    return redirect()->route('inventory.index')->with('success', 'Item deleted successfully.');
}


    // === New methods for API calls via AJAX ===

    public function apiUpdate(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'quantity' => 'required|integer',
                'price' => 'required|numeric',
                'location' => 'required|string|max:255',
            ]);

            $item = Inventory::findOrFail($id);
            $item->update($validated);

            AuditLog::create([
                'inventory_id' => $item->id,
                'action' => 'updated via API',
                'user' => 'admin',
                'location' => $validated['location'],
            ]);

            return response()->json(['success' => true, 'item' => $item]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function apiDelete($id)
    {
        try {
            $item = Inventory::findOrFail($id);

            AuditLog::create([
                'inventory_id' => $item->id,
                'action' => 'deleted via API',
                'user' => 'admin',
                'location' => $item->location,
            ]);

            $item->delete();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}

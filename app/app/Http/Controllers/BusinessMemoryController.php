<?php

namespace App\Http\Controllers;

use App\Models\BusinessMemory;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BusinessMemoryController extends Controller
{
    public function index()
    {
        $items      = BusinessMemory::orderBy('category')->orderBy('id')->get();
        $categories = BusinessMemory::categories();
        $grouped    = $items->groupBy('category');

        return view('business.index', compact('items', 'categories', 'grouped'));
    }

    public function store(Request $request)
    {
        if ($request->input('category') === 'custom') {
            $request->merge(['category' => strtolower($request->input('custom_category', ''))]);
        } else {
            $request->merge(['category' => strtolower($request->input('category', ''))]);
        }

        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'key'      => 'required|string|max:100',
            'value'    => 'required|string',
        ]);

        BusinessMemory::create(array_merge($validated, ['active' => true]));
        ActivityLog::record('business_memory', "Added business memory: {$validated['key']}");

        return back()->with('success', 'Entry added successfully!');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'key'      => 'required|string|max:100',
            'value'    => 'required|string',
            'active'   => 'nullable|boolean',
        ]);

        $item         = BusinessMemory::findOrFail($id);
        $validated['active'] = $request->boolean('active', true);
        $item->update($validated);

        return back()->with('success', 'Entry updated!');
    }

    public function destroy(int $id)
    {
        $item = BusinessMemory::findOrFail($id);
        ActivityLog::record('business_memory', "Deleted business memory: {$item->key}");
        $item->delete();

        return back()->with('success', 'Entry deleted.');
    }

    public function toggleActive(int $id)
    {
        $item = BusinessMemory::findOrFail($id);
        $item->update(['active' => !$item->active]);

        return response()->json(['active' => $item->active]);
    }
}

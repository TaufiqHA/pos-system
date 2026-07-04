<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['id'] = (string) Str::uuid();

        Branch::create($validated);

        return redirect()->back()->with('success', 'Branch created successfully.');
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('success', 'Branch updated successfully.');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return redirect()->back()->with('success', 'Branch deleted successfully.');
    }
}

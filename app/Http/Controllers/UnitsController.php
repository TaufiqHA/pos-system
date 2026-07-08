<?php

namespace App\Http\Controllers;

use App\Models\Units;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UnitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $units = Units::all();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($units);
        }

        return view('admin.units', compact('units'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('units.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:units,name|max:255',
        ]);

        $unit = Units::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Unit berhasil dibuat',
                'data' => $unit,
            ], 201);
        }

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $unit = Units::findOrFail($id);

        return response()->json($unit);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return redirect()->route('units.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $unit = Units::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,'.$id,
        ]);

        $unit->update([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Unit berhasil diupdate',
                'data' => $unit,
            ]);
        }

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $unit = Units::findOrFail($id);
        $unit->delete();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Unit berhasil dihapus',
            ]);
        }

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }
}

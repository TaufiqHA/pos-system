<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Outlets;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OutletsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $outlets = Outlets::with('branch')->get();
        $branches = Branch::all();
        $adminBranchIds = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->pluck('branch_id')->toArray();

        if ($request->wantsJson()) {
            return response()->json(['outlets' => $outlets, 'branches' => $branches, 'adminBranchIds' => $adminBranchIds]);
        }

        return view('cabang.outlet', compact('outlets', 'branches', 'adminBranchIds'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|string|exists:branches,id',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            // New fields for user creation
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // Create Outlet first
        $validated['id'] = (string) Str::uuid();
        $outlet = Outlets::create($validated);

        // Create associated User with role "outlet"
        $outletUserData = [
            'id' => (string) Str::uuid(),
            'role_id' => Role::where('name', 'outlet')->value('id'),
            'branch_id' => $validated['branch_id'],
            'outlet_id' => $outlet->id,
            'name' => $validated['name'], // use outlet name as user name
            'email' => $validated['email'],
            'password' => $validated['password'], // will be hashed via mutator
            'status' => 'active', // default status
        ];
        User::create($outletUserData);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Outlet berhasil dibuat',
                'data' => $outlet,
            ], 201);
        }

        return redirect()->back()->with('success', 'Outlet berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $outlet = Outlets::with('branch')->findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json($outlet);
        }

        return view('admin.outlets-show', compact('outlet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $outlet = Outlets::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => 'sometimes|required|string|exists:branches,id',
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        $outlet->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Outlet berhasil diupdate',
                'data' => $outlet,
            ]);
        }

        return redirect()->back()->with('success', 'Outlet berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, $id)
    {
        $outlet = Outlets::findOrFail($id);
        $outlet->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Outlet berhasil dihapus',
            ]);
        }

        return redirect()->back()->with('success', 'Outlet berhasil dihapus.');
    }

    /**
     * Alias for delete (standard Laravel resource method).
     */
    public function destroy(Request $request, $id)
    {
        return $this->delete($request, $id);
    }
}

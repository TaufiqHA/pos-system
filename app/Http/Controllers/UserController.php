<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'branch.wilayah', 'outlet.branch.wilayah']);

        // Search filter (name, email, phone)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Branch filter (direct or via outlet)
        if ($request->filled('branch_id')) {
            $branchId = $request->input('branch_id');
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->orWhereHas('outlet', function ($oq) use ($branchId) {
                        $oq->where('branch_id', $branchId);
                    });
            });
        }

        // Wilayah filter (via branch or outlet branch)
        if ($request->filled('wilayah_id')) {
            $wilayahId = $request->input('wilayah_id');
            $query->where(function ($q) use ($wilayahId) {
                $q->whereHas('branch', function ($bq) use ($wilayahId) {
                    $bq->where('wilayah_id', $wilayahId);
                })
                    ->orWhereHas('outlet.branch', function ($bq) use ($wilayahId) {
                        $bq->where('wilayah_id', $wilayahId);
                    });
            });
        }

        $users = $query->get();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        $roles = Role::all();

        // Exclude branches connected to admin users
        $adminBranchIds = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->whereNotNull('branch_id')->pluck('branch_id')->all();

        $branches = Branch::whereNotIn('id', $adminBranchIds)->orderBy('name')->get();
        $wilayahs = Wilayah::orderBy('name')->get();

        return view('admin.users', compact('users', 'roles', 'branches', 'wilayahs'));
    }

    public function create()
    {
        return redirect()->route('users.index', ['action' => 'create']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:3|max:6',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['id'] = (string) Str::uuid();

        $user = User::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'User berhasil dibuat', 'data' => $user], 201);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat');
    }

    public function show($id)
    {
        $user = User::with('role', 'branch')->findOrFail($id);

        return response()->json($user);
    }

    public function edit($id)
    {
        return redirect()->route('users.index', ['action' => 'edit', 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$id,
            'password' => 'nullable|string|min:3|max:6',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive',
            'phone' => 'nullable|string|max:20',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'User berhasil diupdate', 'data' => $user]);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'User berhasil dihapus']);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}

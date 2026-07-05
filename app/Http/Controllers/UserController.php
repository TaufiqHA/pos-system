<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('role', 'branch')->get();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        $roles = Role::all();
        $branches = Branch::all();

        return view('admin.users', compact('users', 'roles', 'branches'));
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
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive',
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
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive',
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

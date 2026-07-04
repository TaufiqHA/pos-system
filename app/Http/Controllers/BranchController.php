<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::whereDoesntHave('users', function ($q) {
            $q->whereHas('role', function ($q2) {
                $q2->where('name', 'admin');
            });
        })->with('wilayah');

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $branches = $query->get();
        $wilayahs = \App\Models\Wilayah::all();
        
        return view('admin.branch', compact('branches', 'wilayahs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'wilayah_id' => 'nullable|string|exists:wilayahs,id',
            'notes' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $branchId = (string) Str::uuid();
        $validated['id'] = $branchId;

        $email = $validated['email'];
        $password = $validated['password'];
        unset($validated['email'], $validated['password']);

        Branch::create($validated);

        $cabangRole = Role::where('name', 'cabang')->first();

        $user = new User();
        $user->id = (string) Str::uuid();
        $user->name = 'Admin ' . $request->name;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->branch_id = $branchId;
        $user->role_id = $cabangRole?->id;
        $user->status = 'active';
        $user->save();

        return redirect()->back()->with('success', 'Branch dan Akun berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'wilayah_id' => 'nullable|string|exists:wilayahs,id',
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

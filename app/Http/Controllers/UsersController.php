<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->paginate(10)->appends($request->only('search'));
        return view('pages.user.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('pages.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
            ]);
            DB::commit();
            return redirect()->route('user.index')->with('status', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.index')->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return view('pages.user.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('pages.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'nullable|exists:roles,id',
        ]);

        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->update($request->only('name', 'email', 'role_id'));
            DB::commit();
            return redirect()->route('user.index')->with('status', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.index')->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            User::findOrFail($id)->delete();
            return redirect()->route('user.index')->with('status', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('user.index')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}

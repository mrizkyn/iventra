<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Menampilkan halaman manajemen user.
     */
    public function index()
    {
        return Inertia::render('Users/Index', [
            'users' => User::with('role')->latest()->paginate(10),
            'roles' => Role::all(['id', 'role_name']),
        ]);
    }

    /**
     * Menyimpan user baru yang dibuat oleh Owner.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('users.index')->with('message', 'User created successfully.');
    }

    /**
     * Mengupdate role seorang user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update(['role_id' => $request->role_id]);

        return redirect()->back()->with('message', 'User role updated successfully.');
    }
}

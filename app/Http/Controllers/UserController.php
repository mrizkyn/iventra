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
public function index(Request $request)
{
    $query = User::with('role')->latest('id');

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereHas('role', function ($q2) use ($search) {
                  $q2->where('role_name', 'like', "%{$search}%");
              });
        });
    }

    return Inertia::render('Users/Index', [
        'users' => $query->paginate(10)->withQueryString(),
        'roles' => Role::select('id', 'role_name')->get(),
        'filters' => $request->only('search'),
        'flash' => [
            'success' => session('success')
        ]
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

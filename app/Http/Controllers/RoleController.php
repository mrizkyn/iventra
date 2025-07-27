<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
   public function index(Request $request)
{
    $query = Role::withCount('users')->latest('id');

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('role_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    return Inertia::render('Roles/Index', [
        'roles' => $query->paginate(10)->withQueryString(),
        'filters' => $request->only('search'),
        'flash' => [
            'success' => session('message'),
            'error' => session('error'),
        ],
    ]);
}

    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
            'description' => 'nullable|string',
        ]);
        Role::create($request->all());
        return redirect()->route('roles.index')->with('message', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $role->id,
            'description' => 'nullable|string',
        ]);
        $role->update($request->all());
        return redirect()->route('roles.index')->with('message', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Cannot delete role with assigned users.']);
        }
        $role->delete();
        return redirect()->route('roles.index')->with('message', 'Role deleted successfully.');
    }
}

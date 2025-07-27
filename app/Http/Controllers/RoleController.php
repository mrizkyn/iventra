<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        return Inertia::render('Roles/Index', [
            'roles' => Role::withCount('users')->latest()->paginate(10)
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

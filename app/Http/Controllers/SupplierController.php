<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
   public function index(Request $request)
{
    $query = Supplier::query()->latest();

    // Search if search input exists
    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('supplier_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    return Inertia::render('Suppliers/Index', [
        'suppliers' => $query->paginate(10)->withQueryString(),
        'filters' => $request->only('search')
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:suppliers,email',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('message', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:suppliers,email,' . $supplier->id,
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')->with('message', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete a supplier with existing purchases.']);
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')->with('message', 'Supplier deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:suppliers,id',
        ]);

        $deletableIds = [];
        foreach ($request->ids as $id) {
            $supplier = Supplier::find($id);
            if ($supplier && !$supplier->purchases()->exists()) {
                $deletableIds[] = $id;
            }
        }

        $deletedCount = count($deletableIds);
        if ($deletedCount > 0) {
            Supplier::whereIn('id', $deletableIds)->delete();
        }

        $notDeletedCount = count($request->ids) - $deletedCount;
        $message = "{$deletedCount} supplier(s) deleted successfully.";

        if ($notDeletedCount > 0) {
            // Kirim pesan error jika ada yang tidak bisa dihapus
            $errorMessage = "{$notDeletedCount} supplier(s) could not be deleted because they have existing purchases.";
            return redirect()->route('suppliers.index')->with('message', $message)->withErrors(['error' => $errorMessage]);
        }

        return redirect()->route('suppliers.index')->with('message', $message);
    }
}

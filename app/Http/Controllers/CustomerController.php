<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
  public function index(Request $request)
{
    $query = Customer::query()->latest();

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    return Inertia::render('Customers/Index', [
        'customers' => $query->paginate(10)->withQueryString(),
        'filters' => $request->only('search')
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email',
        ]);

        Customer::create($validated);
        return redirect()->route('customers.index')->with('message', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($validated);
        return redirect()->route('customers.index')->with('message', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete a customer with existing sales records.']);
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('message', 'Customer deleted successfully.');
    }

    /**
     * Menghapus beberapa customer sekaligus.
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:customers,id',
        ]);

        $deletableIds = [];
        foreach ($request->ids as $id) {
            $customer = Customer::find($id);
            if ($customer && !$customer->sales()->exists()) {
                $deletableIds[] = $id;
            }
        }

        $deletedCount = count($deletableIds);
        if ($deletedCount > 0) {
            Customer::whereIn('id', $deletableIds)->delete();
        }

        $notDeletedCount = count($request->ids) - $deletedCount;
        $message = "{$deletedCount} customer(s) deleted successfully.";
        if ($notDeletedCount > 0) {
            $errorMessage = "{$notDeletedCount} customer(s) could not be deleted because they have existing sales records.";
            return redirect()->route('customers.index')->with('message', $message)->withErrors(['error' => $errorMessage]);
        }

        return redirect()->route('customers.index')->with('message', $message);
    }
}

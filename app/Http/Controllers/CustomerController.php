<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Menampilkan halaman utama manajemen customer.
     */
    public function index()
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::latest()->paginate(10)
        ]);
    }

    /**
     * Menyimpan customer baru.
     */
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

    /**
     * Mengupdate data customer.
     */
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

    /**
     * Menghapus data customer.
     */
    public function destroy(Customer $customer)
    {
        // Mencegah penghapusan jika customer memiliki data penjualan.
        if ($customer->sales()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete a customer with existing sales records.']);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('message', 'Customer deleted successfully.');
    }
}

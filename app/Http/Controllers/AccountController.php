<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        return Inertia::render('Accounts/Index', [
            'accounts' => Account::orderBy('category')->orderBy('account_code')->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_code' => 'required|string|max:50|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
        ]);

        Account::create($request->all());
        return redirect()->route('accounts.index')->with('message', 'Account created successfully.');
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'account_code' => 'required|string|max:50|unique:accounts,account_code,' . $account->id,
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
        ]);

        $account->update($request->all());
        return redirect()->route('accounts.index')->with('message', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        // Tambahkan validasi jika akun sudah pernah digunakan dalam transaksi
        if ($account->cashTransactions()->exists()) {
             return redirect()->back()->withErrors(['error' => 'Cannot delete account with existing transactions.']);
        }
        $account->delete();
        return redirect()->route('accounts.index')->with('message', 'Account deleted successfully.');
    }
}

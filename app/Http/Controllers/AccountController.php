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
            'accounts' => Account::orderBy('category')
                ->orderBy('account_code')
                ->paginate(15)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:accounts,account_code',
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
        ]);

        Account::create($validated);

        return redirect()->route('accounts.index')->with('message', 'Account created successfully.');
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:accounts,account_code,' . $account->id,
            'account_name' => 'required|string|max:255',
            'category' => 'required|string|in:Asset,Liability,Equity,Revenue,Expense',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.index')->with('message', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        if ($account->cashTransactions()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete account with existing transactions.']);
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('message', 'Account deleted successfully.');
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:accounts,id',
        ]);

        $deletableIds = [];
        foreach ($request->ids as $id) {
            $account = Account::find($id);
            if ($account && !$account->cashTransactions()->exists()) {
                $deletableIds[] = $id;
            }
        }

        $deletedCount = count($deletableIds);
        if ($deletedCount > 0) {
            Account::whereIn('id', $deletableIds)->delete();
        }

        $notDeletedCount = count($request->ids) - $deletedCount;
        $message = "{$deletedCount} account(s) deleted successfully.";

        if ($notDeletedCount > 0) {
            $errorMessage = "{$notDeletedCount} account(s) could not be deleted because they have existing transactions.";
            return redirect()->route('accounts.index')->with('message', $message)->withErrors(['error' => $errorMessage]);
        }

        return redirect()->route('accounts.index')->with('message', $message);
    }
}

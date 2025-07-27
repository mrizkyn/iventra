<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashTransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('CashTransactions/Index', [
            'transactions' => CashTransaction::with(['account', 'user'])->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return Inertia::render('CashTransactions/Create', [
            'accounts' => Account::orderBy('account_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|string|in:In,Out',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        CashTransaction::create($request->all() + ['user_id' => auth()->id()]);

        return redirect()->route('cash-transactions.index')->with('message', 'Cash transaction recorded.');
    }
}

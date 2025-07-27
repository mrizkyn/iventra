<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CashTransactionController extends Controller
{
    /**
     * Menampilkan halaman buku besar kas.
     */
   public function index(Request $request)
{
    $query = CashTransaction::with(['account', 'user'])
        ->latest('transaction_date')
        ->latest('id');

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('transaction_date', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('user', function ($q2) use ($search) {
                  $q2->where('name', 'like', "%{$search}%");
              });
        });
    }

    return Inertia::render('CashTransactions/Index', [
        'transactions' => $query->paginate(15)->withQueryString(),
        'filters' => $request->only('search'), // <== agar input tetap tersimpan
    ]);
}

    /**
     * Menampilkan form untuk membuat transaksi kas baru.
     */
    public function create()
    {
        return Inertia::render('CashTransactions/Create', [
            'accounts' => Account::orderBy('account_name')->get(),
        ]);
    }

    /**
     * Menyimpan transaksi kas baru dan menghitung saldo berjalan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|string|in:In,Out',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            // Kunci tabel untuk mencegah race condition
            DB::table('cash_transactions')->lockForUpdate();

            // 1. Ambil saldo terakhir
            $lastTransaction = CashTransaction::where('account_id', $request->account_id)
                                              ->latest('transaction_date')
                                              ->latest('id')
                                              ->first();
            $lastBalance = $lastTransaction ? $lastTransaction->balance : 0;

            // 2. Hitung debit, kredit, dan saldo baru
            $debit = $request->type === 'In' ? $request->amount : 0;
            $credit = $request->type === 'Out' ? $request->amount : 0;
            $newBalance = $lastBalance + $debit - $credit;

            // 3. Simpan transaksi baru beserta saldonya
            CashTransaction::create([
                'transaction_date' => $request->transaction_date,
                'account_id' => $request->account_id,
                'description' => $request->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance, // Simpan saldo yang sudah dihitung
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('cash-transactions.index')->with('message', 'Cash transaction recorded.');
    }
}

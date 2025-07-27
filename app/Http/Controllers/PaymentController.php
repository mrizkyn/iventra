<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PaymentController extends Controller
{
    /**
     * Menampilkan halaman terpusat untuk semua transaksi pembayaran.
     */
    public function index()
    {
        return Inertia::render('Payments/Index', [
            // Eager load relasi polimorfik jika Anda mengaturnya,
            // atau muat manual seperti ini untuk kesederhanaan.
            'payments' => Payment::with(['purchase.supplier', 'sale.customer'])->latest()->paginate(15),
        ]);
    }

    /**
     * Menyimpan pembayaran untuk transaksi Pembelian (Hutang).
     */
   public function storePurchasePayment(Request $request, Purchase $purchase)
{
    $request->validate([
        'payment_date' => 'required|date',
        'amount_paid' => 'required|numeric|min:0.01|max:' . $purchase->remaining_debt,
        'payment_method' => 'required|string',
    ]);

    DB::transaction(function () use ($request, $purchase) {
        // 1. Catat pembayaran di tabel payments
        Payment::create([
            'transaction_type' => 'Debt',
            'transaction_id' => $purchase->id,
            'payment_date' => $request->payment_date,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
        ]);

        // 2. Update sisa hutang dan status di tabel purchases
        $newRemainingDebt = $purchase->remaining_debt - $request->amount_paid;
        $newPaymentStatus = $newRemainingDebt <= 0 ? 'Paid' : 'DP';

        $purchase->update([
            'remaining_debt' => $newRemainingDebt,
            'payment_status' => $newPaymentStatus,
        ]);

        // 3. Catat pergerakan kas keluar (kredit) di cash_transactions
        DB::table('cash_transactions')->lockForUpdate();
        $account_id = 3;
        $lastTransaction = CashTransaction::where('account_id', $account_id)->latest('transaction_date')->latest('id')->first();
        $lastBalance = $lastTransaction ? $lastTransaction->balance : 0;
        $newBalance = $lastBalance - $request->amount_paid;

        CashTransaction::create([
            'transaction_date' => $request->payment_date,
            'account_id' => $account_id,
            'description' => 'Payment for Purchase ' . $purchase->purchase_number,
            'debit' => 0,
            'credit' => $request->amount_paid,
            'balance' => $newBalance,
            'related_transaction_id' => $purchase->id,
            'user_id' => auth()->id(),
        ]);
    });

    return redirect()->back()->with('message', 'Payment recorded successfully.');
}

    /**
     * Menyimpan pembayaran untuk transaksi Penjualan (Piutang).
     */
 public function storeSalePayment(Request $request, Sale $sale)
{
    $request->validate([
        'payment_date' => 'required|date',
        'amount_paid' => 'required|numeric|min:0.01|max:' . $sale->remaining_receivable,
        'payment_method' => 'required|string',
    ]);

    DB::transaction(function () use ($request, $sale) {
        // 1. Catat pembayaran di tabel payments
        Payment::create([
            'transaction_type' => 'Receivable',
            'transaction_id' => $sale->id,
            'payment_date' => $request->payment_date,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
        ]);

        // 2. Update sisa piutang dan status di tabel sales
        $newRemainingReceivable = $sale->remaining_receivable - $request->amount_paid;
        $newPaymentStatus = $newRemainingReceivable <= 0 ? 'Paid' : 'DP';

        $sale->update([
            'remaining_receivable' => $newRemainingReceivable,
            'payment_status' => $newPaymentStatus,
        ]);

        // 3. Catat pergerakan kas masuk (debit) di cash_transactions
        DB::table('cash_transactions')->lockForUpdate();
        $account_id = 1; // ID Akun Kas & Bank
        $lastTransaction = CashTransaction::where('account_id', $account_id)->latest('transaction_date')->latest('id')->first();
        $lastBalance = $lastTransaction ? $lastTransaction->balance : 0;
        $newBalance = $lastBalance + $request->amount_paid;

        CashTransaction::create([
            'transaction_date' => $request->payment_date,
            'account_id' => $account_id,
            'description' => 'Payment for Sale ' . $sale->sale_number,
            'debit' => $request->amount_paid,
            'credit' => 0,
            'balance' => $newBalance,
            'related_transaction_id' => $sale->id,
            'user_id' => auth()->id(),
        ]);
    });

    return redirect()->back()->with('message', 'Payment for sale recorded successfully.');
}
}

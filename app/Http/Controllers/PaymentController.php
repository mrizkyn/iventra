<?php

namespace App\Http\Controllers;

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
            Payment::create([
                'transaction_type' => 'Debt',
                'transaction_id' => $purchase->id,
                'payment_date' => $request->payment_date,
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
            ]);

            $newRemainingDebt = $purchase->remaining_debt - $request->amount_paid;
            $newPaymentStatus = $newRemainingDebt <= 0 ? 'Paid' : 'DP';

            $purchase->update([
                'remaining_debt' => $newRemainingDebt,
                'payment_status' => $newPaymentStatus,
            ]);
        });

        return redirect()->back()->with('message', 'Payment for purchase recorded successfully.');
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
            Payment::create([
                'transaction_type' => 'Receivable',
                'transaction_id' => $sale->id,
                'payment_date' => $request->payment_date,
                'amount_paid' => $request->amount_paid,
                'payment_method' => $request->payment_method,
            ]);

            $newRemainingReceivable = $sale->remaining_receivable - $request->amount_paid;
            $newPaymentStatus = $newRemainingReceivable <= 0 ? 'Paid' : 'DP';

            $sale->update([
                'remaining_receivable' => $newRemainingReceivable,
                'payment_status' => $newPaymentStatus,
            ]);
        });

        return redirect()->back()->with('message', 'Payment for sale recorded successfully.');
    }
}

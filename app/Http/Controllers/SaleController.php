<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Sales/Index', [
            'sales' => Sale::with('customer')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return Inertia::render('Sales/Create', [
            'customers' => Customer::all(['id', 'customer_name']),
            // Kirim produk yang ada stoknya saja
            'products' => Product::where('current_stock', '>', 0)->get(['id', 'product_name', 'product_code', 'sell_price', 'current_stock']),
        ]);
    }

   public function store(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'sale_date' => 'required|date',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|integer|min:1',
        'down_payment' => 'required|numeric|min:0',
        'payment_method' => 'required_if:down_payment,>,0|nullable|string',
    ]);

    try {
        DB::transaction(function () use ($request) {
            $user = auth()->user();

            // Validasi stok sebelum membuat transaksi
            foreach ($request->details as $detail) {
                $product = Product::find($detail['product_id']);
                if ($product->current_stock < $detail['quantity']) {
                    throw ValidationException::withMessages([
                        'details' => "Stock for {$product->product_name} is not sufficient. Only {$product->current_stock} left.",
                    ]);
                }
            }

            $totalPrice = collect($request->details)->sum(fn($d) => $d['quantity'] * $d['sell_price_per_unit']);
            $downPayment = $request->down_payment;
            $remainingReceivable = $totalPrice - $downPayment;

            $paymentStatus = 'Unpaid';
            if ($downPayment > 0) {
                $paymentStatus = $remainingReceivable <= 0 ? 'Paid' : 'DP';
            }

            // ## LOGIKA AUTO-APPROVE UNTUK OWNER ##
            $isOwner = $user->role?->role_name === 'Owner';
            $approvalStatus = $isOwner ? 'Approved' : 'Pending';
            $approvedBy = $isOwner ? $user->id : null;
            $approvedAt = $isOwner ? now() : null;

            // 1. Buat data Penjualan
            $sale = Sale::create([
                'sale_number' => 'SO/' . date('Ymd') . '/' . (Sale::count() + 1),
                'customer_id' => $request->customer_id,
                'user_id' => $user->id,
                'sale_date' => $request->sale_date,
                'total_price' => $totalPrice,
                'down_payment' => $downPayment,
                'remaining_receivable' => $remainingReceivable,
                'payment_status' => $paymentStatus,
                'approval_status' => $approvalStatus, // Dinamis
                'approved_by' => $approvedBy,         // Dinamis
                'approved_at' => $approvedAt,         // Dinamis
            ]);

            // 2. Buat detail penjualan
            foreach ($request->details as $detail) {
                $sale->details()->create($detail);

                // 3. Jika auto-approved oleh Owner, langsung kurangi stok
                if ($isOwner) {
                    Product::find($detail['product_id'])->decrement('current_stock', $detail['quantity']);
                }
            }

            // 4. Jika ada pembayaran, catat di tabel payments
            if ($downPayment > 0) {
                Payment::create([
                    'transaction_type' => 'Receivable',
                    'transaction_id' => $sale->id,
                    'payment_date' => $request->sale_date,
                    'amount_paid' => $downPayment,
                    'payment_method' => $request->payment_method,
                ]);
            }
        });
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Transaction failed! ' . $e->getMessage()]);
    }

    $message = auth()->user()->role?->role_name === 'Owner'
        ? 'Sale recorded and auto-approved. Stock has been updated.'
        : 'Sale recorded and waiting for approval.';

    return redirect()->route('sales.index')->with('message', $message);
}

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'details.product', 'payments']);
        return Inertia::render('Sales/Show', compact('sale'));
    }

    /**
     * Metode BARU untuk menyetujui penjualan (HANYA OWNER).
     */
    public function approve(Sale $sale)
    {
        if ($sale->approval_status !== 'Pending') {
            return redirect()->back()->withErrors(['error' => 'This sale has already been processed.']);
        }

        try {
            DB::transaction(function () use ($sale) {
                // 1. Update status approval
                $sale->update([
                    'approval_status' => 'Approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // 2. Kurangi stok produk
                foreach ($sale->details as $detail) {
                    Product::find($detail->product_id)->decrement('current_stock', $detail->quantity);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Approval failed! ' . $e->getMessage()]);
        }

        return redirect()->route('sales.index')->with('message', 'Sale has been approved and stock updated.');
    }
}

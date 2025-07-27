<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Menampilkan daftar semua pembelian.
     */
   public function index(Request $request)
{
    $purchases = Purchase::with(['supplier', 'user'])
        ->when($request->input('search'), function ($query, $search) {
            $query->where('purchase_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn($q) => $q->where('supplier_name', 'like', "%{$search}%"));
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Purchases/Index', [
        'purchases' => $purchases,
        'filters' => $request->only(['search']),
    ]);
}
    /**
     * Menampilkan form untuk membuat pembelian baru.
     */
    public function create()
    {
        return Inertia::render('Purchases/Create', [
            'suppliers' => Supplier::all(['id', 'supplier_name']),
            'products' => Product::all(['id', 'product_name', 'product_code', 'last_buy_price']),
        ]);
    }

    /**
     * Menyimpan pembelian baru ke database.
     */
   public function store(Request $request)
{
    $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_date' => 'required|date',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|integer|min:1',
        'details.*.buy_price_per_unit' => 'required|numeric|min:0',
        'down_payment' => 'required|numeric|min:0',
        'payment_method' => 'required_if:down_payment,>,0|nullable|string',
    ]);

    try {
        DB::transaction(function () use ($request) {
            $user = auth()->user();
            $totalPrice = collect($request->details)->sum(fn($d) => $d['quantity'] * $d['buy_price_per_unit']);
            $downPayment = $request->down_payment;
            $remainingDebt = $totalPrice - $downPayment;

            // Tentukan status pembayaran
            $paymentStatus = 'Unpaid';
            if ($downPayment > 0) {
                $paymentStatus = $remainingDebt <= 0 ? 'Paid' : 'DP';
            }

            // ## LOGIKA AUTO-APPROVE UNTUK OWNER ##
            $isOwner = $user->role?->role_name === 'Owner';
            $approvalStatus = $isOwner ? 'Approved' : 'Pending';
            $approvedBy = $isOwner ? $user->id : null;
            $approvedAt = $isOwner ? now() : null;

            // 1. Buat data Pembelian
            $purchase = Purchase::create([
                'purchase_number' => 'PO/' . date('Ymd') . '/' . (Purchase::count() + 1),
                'supplier_id' => $request->supplier_id,
                'user_id' => $user->id,
                'purchase_date' => $request->purchase_date,
                'total_price' => $totalPrice,
                'down_payment' => $downPayment,
                'remaining_debt' => $remainingDebt,
                'payment_status' => $paymentStatus,
                'approval_status' => $approvalStatus, // Dinamis berdasarkan role
                'approved_by' => $approvedBy,         // Dinamis berdasarkan role
                'approved_at' => $approvedAt,         // Dinamis berdasarkan role
            ]);

            // 2. Buat detail pembelian
            foreach ($request->details as $detail) {
                $purchase->details()->create($detail);

                // 3. Jika auto-approved oleh Owner, langsung update stok
                if ($isOwner) {
                    $product = Product::find($detail['product_id']);
                    $product->increment('current_stock', $detail['quantity']);
                    $product->update(['last_buy_price' => $detail['buy_price_per_unit']]);
                }
            }

            // 4. Jika ada pembayaran, catat di tabel payments
            if ($downPayment > 0) {
                Payment::create([
                    'transaction_type' => 'Debt',
                    'transaction_id' => $purchase->id,
                    'payment_date' => $request->purchase_date,
                    'amount_paid' => $downPayment,
                    'payment_method' => $request->payment_method,
                ]);
            }
        });
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Transaction failed! ' . $e->getMessage()]);
    }

    // Pesan notifikasi dinamis
    $message = auth()->user()->role?->role_name === 'Owner'
        ? 'Purchase recorded and auto-approved. Stock has been updated.'
        : 'Purchase recorded and waiting for approval.';

    return redirect()->route('purchases.index')->with('message', $message);
}

    /**
     * Menampilkan detail satu pembelian.
     */
  public function show(Purchase $purchase)
{
    $purchase->load([
        'supplier',
        'user',
        'details.product',
        'payments',
        'returns.details' // <-- TAMBAHKAN INI
    ]);
    return Inertia::render('Purchases/Show', compact('purchase'));
}

     public function approve(Purchase $purchase)
    {
        // Pastikan hanya pembelian yang pending yang bisa diapprove
        if ($purchase->approval_status !== 'Pending') {
            return redirect()->back()->withErrors(['error' => 'This purchase has already been processed.']);
        }

        try {
            DB::transaction(function () use ($purchase) {
                // 1. Update status approval di pembelian
                $purchase->update([
                    'approval_status' => 'Approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                // 2. Update stok produk dan harga beli
                foreach ($purchase->details as $detail) {
                    $product = Product::find($detail->product_id);
                    $product->increment('current_stock', $detail->quantity);
                    $product->update(['last_buy_price' => $detail->buy_price_per_unit]);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Approval failed! ' . $e->getMessage()]);
        }

        return redirect()->route('purchases.index')->with('message', 'Purchase has been approved successfully and stock updated.');
    }
}

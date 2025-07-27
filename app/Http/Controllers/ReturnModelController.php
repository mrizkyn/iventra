<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReturnModel;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReturnModelController extends Controller
{
    public function index()
    {
        return Inertia::render('Returns/Index', [
            'returns' => ReturnModel::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        // Ambil data transaksi yang sudah approved & belum sepenuhnya diretur (logika kompleks bisa ditambahkan)
        return Inertia::render('Returns/Create', [
            'purchases' => Purchase::where('approval_status', 'Approved')->get(['id', 'purchase_number']),
            'sales' => Sale::where('approval_status', 'Approved')->get(['id', 'sale_number']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'return_date' => 'required|date',
            'return_type' => 'required|string|in:Purchase,Sale',
            'transaction_id' => 'required|integer',
            'reason' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Buat record master Retur
                $return = ReturnModel::create([
                    'return_number' => 'RT/' . date('Ymd') . '/' . (ReturnModel::count() + 1),
                    'return_date' => $request->return_date,
                    'return_type' => $request->return_type,
                    'related_transaction_id' => $request->transaction_id,
                    'reason' => $request->reason,
                ]);

                // 2. Simpan detail dan sesuaikan stok
                foreach ($request->details as $detail) {
                    $return->details()->create($detail);
                    $product = Product::find($detail['product_id']);

                    if ($request->return_type === 'Sale') {
                        // Barang kembali dari customer, stok bertambah
                        $product->increment('current_stock', $detail['quantity']);
                    } else { // Purchase
                        // Kita kembalikan barang ke supplier, stok berkurang
                        $product->decrement('current_stock', $detail['quantity']);
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to record return! ' . $e->getMessage()]);
        }

        return redirect()->route('returns.index')->with('message', 'Return has been recorded successfully.');
    }

    public function show(ReturnModel $return)
    {
        $return->load(['details.product']);
        // Muat juga relasi ke transaksi aslinya
        if ($return->return_type === 'Purchase') {
            $return->load('purchase.supplier');
        } else {
            $return->load('sale.customer');
        }
        return Inertia::render('Returns/Show', compact('return'));
    }
}

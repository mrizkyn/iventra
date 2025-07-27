<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockTakeController extends Controller
{
    /**
     * Menampilkan riwayat stok opname.
     */
   public function index(Request $request)
{
   $stock_takes = StockTake::with(['user', 'details.product']) // <-- tambahkan ini
    ->when($request->input('search'), function ($query, $search) {
        $query->where('take_date', 'like', "%{$search}%")
            ->orWhereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
    })
    ->latest()
    ->paginate(10)
    ->withQueryString();

    return Inertia::render('StockTakes/Index', [
        'stock_takes' => $stock_takes,
        'filters' => $request->only(['search']),
    ]);
}

    /**
     * Menampilkan form untuk melakukan stok opname baru.
     */
    public function create()
    {
        return Inertia::render('StockTakes/Create', [
            // Kirim semua produk beserta stok sistem saat ini
            'products' => Product::orderBy('product_name')->get(['id', 'product_name', 'product_code', 'current_stock']),
        ]);
    }

    /**
     * Menyimpan hasil stok opname ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'take_date' => 'required|date',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.physical_stock' => 'required|integer|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Buat record master Stok Opname
                $stockTake = StockTake::create([
                    'take_date' => $request->take_date,
                    'user_id' => auth()->id(),
                    'notes' => $request->notes,
                ]);

                // 2. Simpan setiap detail item yang dihitung
                foreach ($request->details as $detail) {
                    $product = Product::find($detail['product_id']);
                    $systemStock = $product->current_stock;
                    $physicalStock = $detail['physical_stock'];

                    $stockTake->details()->create([
                        'product_id' => $detail['product_id'],
                        'system_stock' => $systemStock,
                        'physical_stock' => $physicalStock,
                        'difference' => $physicalStock - $systemStock,
                    ]);
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to save stock take! ' . $e->getMessage()]);
        }

        return redirect()->route('stock-takes.index')->with('message', 'Stock take results have been recorded successfully.');
    }

    /**
     * Menampilkan detail hasil stok opname.
     */
    public function show(StockTake $stockTake)
    {
        $stockTake->load(['user', 'details.product']);
        return Inertia::render('StockTakes/Show', compact('stockTake'));
    }
}

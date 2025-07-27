<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductionController extends Controller
{
   public function index(Request $request)
{
   $productions = Production::with(['finishedGood', 'materials.rawMaterial'])
    ->when($request->input('search'), function ($query, $search) {
        $query->where('production_number', 'like', "%{$search}%")
            ->orWhereHas('finishedGood', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            });
    })
    ->latest()
    ->paginate(10)
    ->withQueryString();


    return Inertia::render('Productions/Index', [
        'productions' => $productions,
        'filters' => $request->only(['search']),
    ]);
}

    public function create()
    {
        // Pisahkan produk jadi dan bahan baku untuk dropdown
        return Inertia::render('Productions/Create', [
            'finished_goods' => Product::where('product_type', 'Finished Good')->orderBy('product_name')->get(),
            'raw_materials' => Product::where('product_type', 'Raw Material')->where('current_stock', '>', 0)->orderBy('product_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_date' => 'required|date',
            'finished_good_id' => 'required|exists:products,id',
            'quantity_produced' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'materials' => 'required|array|min:1',
            'materials.*.raw_material_id' => 'required|exists:products,id',
            'materials.*.quantity_used' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Validasi stok bahan baku
                foreach ($request->materials as $material) {
                    $product = Product::find($material['raw_material_id']);
                    if ($product->current_stock < $material['quantity_used']) {
                        throw ValidationException::withMessages([
                            'materials' => "Stock for raw material {$product->product_name} is not sufficient.",
                        ]);
                    }
                }

                // 1. Buat record master Produksi
                $production = Production::create([
                    'production_number' => 'PR/' . date('Ymd') . '/' . (Production::count() + 1),
                    'production_date' => $request->production_date,
                    'finished_good_id' => $request->finished_good_id,
                    'quantity_produced' => $request->quantity_produced,
                    'status' => 'Completed',
                    'notes' => $request->notes,
                ]);

                // 2. Kurangi stok bahan baku dan catat di detail
                foreach ($request->materials as $material) {
                    $production->materials()->create($material);
                    Product::find($material['raw_material_id'])->decrement('current_stock', $material['quantity_used']);
                }

                // 3. Tambah stok produk jadi
                Product::find($request->finished_good_id)->increment('current_stock', $request->quantity_produced);
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to record production! ' . $e->getMessage()]);
        }

        return redirect()->route('productions.index')->with('message', 'Production has been recorded and stock updated.');
    }

    public function show(Production $production)
    {
        $production->load(['finishedGood', 'materials.rawMaterial']);
        return Inertia::render('Productions/Show', compact('production'));
    }
}

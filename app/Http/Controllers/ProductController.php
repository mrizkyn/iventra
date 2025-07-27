<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
public function index(Request $request)
{
    $queryFinishedGoods = Product::where('product_type', 'Finished Good');
    $queryRawMaterials = Product::where('product_type', 'Raw Material');
    $queryAssets = Product::where('product_type', 'Asset');

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $filter = function ($query) use ($search) {
            $query->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
        };

        $queryFinishedGoods->where($filter);
        $queryRawMaterials->where($filter);
        $queryAssets->where($filter);
    }

    return Inertia::render('Products/Index', [
        'finished_goods' => $queryFinishedGoods->latest()->paginate(10, ['*'], 'fg_page'),
        'raw_materials' => $queryRawMaterials->latest()->paginate(10, ['*'], 'rm_page'),
        'assets' => $queryAssets->latest()->paginate(10, ['*'], 'as_page'),
        'filters' => $request->only('search'),
    ]);
}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:50|unique:products,product_code',
            'product_type' => 'required|string',
            'unit' => 'required|string|max:50',
            'sell_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        Product::create($validated + ['current_stock' => 0]);
        return redirect()->route('products.index')->with('message', 'Product created successfully.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'product_code' => 'required|string|max:50|unique:products,product_code,' . $product->id,
            'product_type' => 'required|string',
            'unit' => 'required|string|max:50',
            'sell_price' => 'required|numeric|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);
        return redirect()->route('products.index')->with('message', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->sales()->exists() || $product->purchases()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete product with existing sales or purchases.']);
        }
        $product->delete();
        return redirect()->route('products.index')->with('message', 'Product deleted successfully.');
    }
     public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $deletableIds = [];
        foreach ($request->ids as $id) {
            $product = Product::find($id);
            // Cek apakah produk tidak terikat pada transaksi apapun
            if ($product && !$product->purchases()->exists() && !$product->sales()->exists() && !$product->productionMaterials()->exists()) {
                $deletableIds[] = $id;
            }
        }

        $deletedCount = count($deletableIds);
        if ($deletedCount > 0) {
            Product::whereIn('id', $deletableIds)->delete();
        }

        $notDeletedCount = count($request->ids) - $deletedCount;
        $message = "{$deletedCount} item(s) deleted successfully.";

        // Jika ada produk yang tidak bisa dihapus, kirim notifikasi error
        if ($notDeletedCount > 0) {
            $errorMessage = "{$notDeletedCount} item(s) could not be deleted because they are used in existing transactions.";
            return redirect()->route('products.index')->with('message', $message)->withErrors(['error' => $errorMessage]);
        }

        return redirect()->route('products.index')->with('message', $message);
    }
}

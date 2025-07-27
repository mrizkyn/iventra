<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
   public function index()
{
    return Inertia::render('Products/Index', [
        'finished_goods' => Product::where('product_type', 'Finished Good')->latest()->paginate(10, ['*'], 'fg_page'),
        'raw_materials' => Product::where('product_type', 'Raw Material')->latest()->paginate(10, ['*'], 'rm_page'),
        'assets' => Product::where('product_type', 'Asset')->latest()->paginate(10, ['*'], 'as_page'),
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
}

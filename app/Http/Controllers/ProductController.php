<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'wholesalePrices.branch'])->get();
        $categories = Category::all(); // Needed for the modal select dropdown
        $branches = \App\Models\Branch::all();
        return view('admin.products', compact('products', 'categories', 'branches'));
    }

    public function create()
    {
        return redirect()->route('products.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'buy_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
        ]);

        $data = $request->all();
        $data['id'] = Str::uuid()->toString();

        $product = Product::create($data);

        // Automatis create product stock dengan default stok 0 untuk cabang yang sedang login saja
        $branchId = auth()->user()->branch_id ?? \App\Models\Branch::first()?->id;
        if ($branchId) {
            \App\Models\ProductStock::create([
                'id' => (string) Str::uuid(),
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'stock' => 0,
                'minimum_stock' => 0,
                'average_cost' => 0,
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('category');
        return response()->json($product);
    }

    public function edit(Product $product)
    {
        return redirect()->route('products.index');
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'buy_price' => 'required|numeric',
            'sell_price' => 'required|numeric',
        ]);

        $product->update($request->all());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function checkSku(Request $request)
    {
        $sku = $request->query('sku');
        $ignoreId = $request->query('ignore_id');

        if (!$sku) {
            return response()->json(['exists' => false]);
        }

        $query = Product::withTrashed()->whereRaw('LOWER(sku) = ?', [strtolower($sku)]);

        // Abaikan pengecekan ID saat ini jika dalam mode Edit
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $exists = $query->exists();

        return response()->json(['exists' => $exists]);
    }
}


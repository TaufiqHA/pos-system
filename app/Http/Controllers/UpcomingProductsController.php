<?php

namespace App\Http\Controllers;

use App\Models\UpcomingProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpcomingProductsController extends Controller
{
    public function index(Request $request)
    {
        $upcomingProducts = UpcomingProducts::with('creator')->orderBy('created_at', 'desc')->get();

        return response()->json($upcomingProducts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validated['id'] = (string) Str::uuid();
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('upcoming_products', 'public');
            $validated['image'] = '/storage/'.$path;
        }

        $upcomingProduct = UpcomingProducts::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Upcoming product berhasil dibuat',
                'data' => $upcomingProduct,
            ], 201);
        }

        return redirect()->route('upcoming-products.index')->with('success', 'Upcoming product berhasil dibuat');
    }

    public function show($id)
    {
        $upcomingProduct = UpcomingProducts::with('creator')->findOrFail($id);

        return response()->json($upcomingProduct);
    }

    public function update(Request $request, $id)
    {
        $upcomingProduct = UpcomingProducts::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($upcomingProduct->image && str_starts_with($upcomingProduct->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $upcomingProduct->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('upcoming_products', 'public');
            $validated['image'] = '/storage/'.$path;
        } elseif ($request->boolean('delete_image')) {
            if ($upcomingProduct->image && str_starts_with($upcomingProduct->image, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $upcomingProduct->image);
                Storage::disk('public')->delete($oldPath);
            }
            $validated['image'] = null;
        }

        $upcomingProduct->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Upcoming product berhasil diupdate',
                'data' => $upcomingProduct,
            ]);
        }

        return redirect()->route('upcoming-products.index')->with('success', 'Upcoming product berhasil diupdate');
    }

    public function destroy(Request $request, $id)
    {
        $upcomingProduct = UpcomingProducts::findOrFail($id);
        $upcomingProduct->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Upcoming product berhasil dihapus']);
        }

        return redirect()->route('upcoming-products.index')->with('success', 'Upcoming product berhasil dihapus');
    }

    public function delete(Request $request, $id)
    {
        return $this->destroy($request, $id);
    }
}

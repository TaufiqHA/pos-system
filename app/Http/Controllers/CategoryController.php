<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json($categories);
        }

        return view('admin.categories', compact('categories'));
    }

    public function create()
    {
        return redirect()->route('categories.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
        ]);

        $category = Category::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Category created successfully.',
                'data' => $category,
            ], 201);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function edit(Category $category)
    {
        return redirect()->route('categories.index');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Category updated successfully.',
                'data' => $category,
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'message' => 'Category deleted successfully.',
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}

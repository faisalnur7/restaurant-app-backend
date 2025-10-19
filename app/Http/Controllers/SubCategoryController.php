<?php

namespace App\Http\Controllers;

use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of subcategories.
     */
    public function index()
    {
        $subCategories = SubCategory::with('category')->orderBy('id', 'desc')->get();
        return response()->json($subCategories);
    }

    /**
     * Store a newly created subcategory.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory = SubCategory::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Subcategory created successfully',
            'data' => $subCategory,
        ], 201);
    }

    /**
     * Update the specified subcategory.
     */
    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
        ]);

        $subCategory->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Subcategory updated successfully',
            'data' => $subCategory,
        ]);
    }

    /**
     * Remove the specified subcategory.
     */
    public function destroy($id)
    {
        $subCategory = SubCategory::findOrFail($id);
        $subCategory->delete();

        return response()->json([
            'message' => 'Subcategory deleted successfully',
        ]);
    }
}

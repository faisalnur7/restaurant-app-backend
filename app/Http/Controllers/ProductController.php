<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class ProductController extends Controller
{
    public function index()
    {
        return Product::with('category')->latest()->get();
    }

    // public function list(Request $request){
        
    //     $query = Product::query();

    //     if ($request->has('sub_category_id')) {
    //         $query->where('sub_category_id', $request->sub_category_id);
    //     } elseif ($request->has('category_id')) {
    //         $query->where('category_id', $request->category_id);
    //     }

    //     // Optional: eager load relationships for performance
    //     $products = $query
    //         ->orderBy('name')
    //         ->get();

    //     return response()->json($products);
    // }

    public function list(Request $request)
    {
        $query = Product::query();

        if ($request->has('sub_category_id')) {
            $query->where('sub_category_id', $request->sub_category_id);
        } elseif ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query
            ->orderBy('name')
            ->get()
            ->map(function ($product) {
                $product->image = $product->image
                    ? url($product->image)  // Converts to full URL
                    : null;
                return $product;
            });

        return response()->json($products);
    }

    

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        // ✅ Handle image upload manually
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/products'), $filename);
            $data['image'] = 'uploads/products/' . $filename;
        }
        $data['sub_category_id'] = $request->sub_category_id ?? null;

        $product = Product::create($data);
        return response()->json($product, 201);
    }


    public function update(Request $request, Product $product)
    {
        // If React sends "null" or an empty string, treat it as no image
        if ($request->image === "null" || $request->image === null) {
            $request->request->remove('image');
        }

        // Base validation (without image)
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $data['sub_category_id'] = $request->sub_category_id ?? null;

        // ✅ If an image file is uploaded, validate it separately
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            ]);

            // Delete old image if exists
            if ($product->image && File::exists(public_path($product->image))) {
                File::delete(public_path($product->image));
            }

            // Upload new image
            $filename = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('uploads/products'), $filename);
            $data['image'] = url('uploads/products/' . $filename);
        } else {
            // Keep old image if no new file uploaded
            $data['image'] = $product->image;
        }

        // ✅ Ensure boolean conversion from string ("true"/"false")
        $data['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully!',
            'product' => $product,
        ], 200);
    }



    public function destroy(Product $product)
    {
        if ($product->image && File::exists(public_path($product->image))) {
            File::delete(public_path($product->image));
        }

        $product->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Product::paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'nullable|string|unique:products,sku',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'quantity' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Product $product
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'sku' => ['nullable','string', Rule::unique('products','sku')->ignore($product->id)],
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'quantity' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        $product->update($data);
        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}

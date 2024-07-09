<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Categories;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();

        $products->transform(function ($product) {
            $metadata = json_decode($product->metadata);

            if (isset($metadata->brand)) {
                $brand = Brand::where('uuid', $metadata->brand)->first();
                if ($brand) {
                    $product->brand = $brand->title;
                }
            }

            if (isset($product->category_uuid)) {
                $category = Categories::where('uuid', $product->category_uuid)->first();
                if ($category) {
                    $product->category = $category->title;
                }
            }

            return $product;
        });

        return response()->json($products);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_uuid' => 'required|string|exists:categories,uuid',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'required|string',
            'metadata' => 'nullable|json',
        ]);

        $product = new Product();
        $product->uuid = (string) Str::uuid();
        $product->category_uuid = $request->category_uuid;
        $product->title = $request->title;
        $product->price = $request->price;
        $product->description = $request->description;
        $product->metadata = $request->metadata;
        $product->save();

        return response()->json($product, 201);
    }

    /**
     * Display the specified product.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $product = Product::where('uuid', $uuid)->with('category')->firstOrFail();

        $metadata = json_decode($product->metadata);
        if (isset($metadata->brand)) {
            $brand = Brand::where('uuid', $metadata->brand)->first();
            if ($brand) {
                $product->brand = $brand;
            }
        }

        return response()->json($product);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $request->validate([
            'category_uuid' => 'sometimes|required|string|exists:categories,uuid',
            'title' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'metadata' => 'nullable|json',
        ]);

        $product = Product::where('uuid', $uuid)->firstOrFail();

        if ($request->has('category_uuid')) {
            $product->category_uuid = $request->category_uuid;
        }
        if ($request->has('title')) {
            $product->title = $request->title;
        }
        if ($request->has('price')) {
            $product->price = $request->price;
        }
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        if ($request->has('metadata')) {
            $product->metadata = $request->metadata;
        }

        $product->save();

        return response()->json($product);
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete();

        return response()->json(null, 204);
    }
}

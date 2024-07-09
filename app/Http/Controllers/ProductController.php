<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Categories;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/products",
     *     operationId="getProductsList",
     *     tags={"Products"},
     *     summary="Get list of products",
     *     description="Returns list of products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index()
    {
        $products = Product::all();

        $products->transform(function ($product) {
            $metadata = json_decode($product->metadata);

            if (isset($metadata->brand)) {
                $brand = Brand::where('uuid', $metadata->brand)->first();
                if ($brand) {
                    $product->brand_name = $brand->title;
                }
            }

            if (isset($product->category_uuid)) {
                $category = Categories::where('uuid', $product->category_uuid)->first();
                if ($category) {
                    $product->category_name = $category->title;
                }
            }

            return $product;
        });

        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/products/create",
     *     operationId="storeProduct",
     *     tags={"Products"},
     *     summary="Store new product",
     *     description="Stores a new product",
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_uuid' => 'required|string|exists:categories,uuid',
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
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
     * @OA\Get(
     *     path="/api/v1/products/{uuid}",
     *     operationId="getProductByUuid",
     *     tags={"Products"},
     *     summary="Get product information",
     *     description="Returns product data",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Product uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/v1/products/{uuid}",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     summary="Update existing product",
     *     description="Updates a product",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Product uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/products/{uuid}",
     *     operationId="deleteProduct",
     *     tags={"Products"},
     *     summary="Delete existing product",
     *     description="Deletes a product",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Product uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy($uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete();

        return response()->json(null, 204);
    }
}

/**
 * @OA\Schema(
 *     schema="Product",
 *     required={"uuid", "category_uuid", "title", "price", "description"},
 *     @OA\Property(property="uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="category_uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Dog Food"),
 *     @OA\Property(property="price", type="number", format="float", example=14.99),
 *     @OA\Property(property="description", type="string", example="Odio rerum ipsum similique aliquid iste."),
 *     @OA\Property(property="metadata", type="string", example="{\"brand\":\"b2635a08-6447-4025-a0c8-e9c06189d378\",\"image\":\"5e2c1baf-cbf3-4d1d-8aed-c95e03ee00a5\"}")
 * )
 */

/**
 * @OA\Schema(
 *     schema="StoreProductRequest",
 *     required={"category_uuid", "title", "price", "description"},
 *     @OA\Property(property="category_uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Dog Food"),
 *     @OA\Property(property="price", type="number", format="float", example=14.99),
 *     @OA\Property(property="description", type="string", example="Odio rerum ipsum similique aliquid iste."),
 *     @OA\Property(property="metadata", type="string", example="{\"brand\":\"b2635a08-6447-4025-a0c8-e9c06189d378\",\"image\":\"5e2c1baf-cbf3-4d1d-8aed-c95e03ee00a5\"}")
 * )
 */

/**
 * @OA\Schema(
 *     schema="UpdateProductRequest",
 *     required={"category_uuid", "title", "price", "description"},
 *     @OA\Property(property="category_uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Dog Food"),
 *     @OA\Property(property="price", type="number", format="float", example=14.99),
 *     @OA\Property(property="description", type="string", example="Odio rerum ipsum similique aliquid iste."),
 *     @OA\Property(property="metadata", type="string", example="{\"brand\":\"b2635a08-6447-4025-a0c8-e9c06189d378\",\"image\":\"5e2c1baf-cbf3-4d1d-8aed-c95e03ee00a5\"}")
 * )
 */

<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/brands",
     *     operationId="getBrandsList",
     *     tags={"Brands"},
     *     summary="Get list of brands",
     *     description="Returns list of brands",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/brands/create",
     *     operationId="storeBrand",
     *     tags={"Brands"},
     *     summary="Store new brand",
     *     description="Stores a new brand",
     *     security={{"BearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Brand created successfully",
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
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
        ]);

        $brand = new Brand();
        $brand->uuid = (string) Str::uuid();
        $brand->title = $request->title;
        $brand->slug = $request->slug;
        $brand->save();

        return response()->json($brand, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/brands/{uuid}",
     *     operationId="getBrandByUuid",
     *     tags={"Brands"},
     *     summary="Get brand information",
     *     description="Returns brand data",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Brand uuid",
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
        $brand = Brand::where('uuid', $uuid)->firstOrFail();
        return response()->json($brand);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/brands/{uuid}",
     *     operationId="updateBrand",
     *     tags={"Brands"},
     *     summary="Update existing brand",
     *     description="Updates a brand",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Brand uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand updated successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found"
     *     )
     * )
     */
    public function update(Request $request, $uuid)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:brands,slug,' . $uuid . ',uuid',
        ]);

        $brand = Brand::where('uuid', $uuid)->firstOrFail();

        if ($request->has('title')) {
            $brand->title = $request->title;
        }
        if ($request->has('slug')) {
            $brand->slug = $request->slug;
        }

        $brand->save();

        return response()->json($brand);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/brands/{uuid}",
     *     operationId="deleteBrand",
     *     tags={"Brands"},
     *     summary="Delete existing brand",
     *     description="Deletes a brand",
     *     security={{"BearerAuth":{}}},
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Brand uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Brand deleted successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy($uuid)
    {
        $brand = Brand::where('uuid', $uuid)->firstOrFail();
        $brand->delete();

        return response()->json(null, 204);
    }
}

/**
 * @OA\Schema(
 *     schema="Brand",
 *     required={"uuid", "title", "slug"},
 *     @OA\Property(property="uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Brand Title"),
 *     @OA\Property(property="slug", type="string", example="brand-title"),
 * )
 */

/**
 * @OA\Schema(
 *     schema="StoreBrandRequest",
 *     required={"title", "slug"},
 *     @OA\Property(property="title", type="string", example="Brand Title"),
 *     @OA\Property(property="slug", type="string", example="brand-title"),
 * )
 */

/**
 * @OA\Schema(
 *     schema="UpdateBrandRequest",
 *     required={"title", "slug"},
 *     @OA\Property(property="title", type="string", example="Updated Brand Title"),
 *     @OA\Property(property="slug", type="string", example="updated-brand-title"),
 * )
 */

<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

class CategoriesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/categories",
     *      operationId="getCategoriesList",
     *      tags={"Categories"},
     *      summary="Get list of categories",
     *      description="Returns list of categories",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     */
    public function index()
    {
        $categories = Categories::all();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/categories/create",
     *      operationId="storeCategory",
     *      tags={"Categories"},
     *      summary="Store new category",
     *      description="Stores a new category",
     *      security={{"BearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
        ]);

        $category = new Categories();
        $category->uuid = (string) Str::uuid();
        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return response()->json($category, 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/categories/{uuid}",
     *      operationId="getCategoryByUuid",
     *      tags={"Categories"},
     *      summary="Get category information",
     *      description="Returns category data",
     *      security={{"BearerAuth":{}}},
     *      @OA\Parameter(
     *          name="uuid",
     *          description="Category uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     */
    public function show($uuid)
    {
        $category = Categories::where('uuid', $uuid)->firstOrFail();
        return response()->json($category);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/categories/{uuid}",
     *      operationId="updateCategory",
     *      tags={"Categories"},
     *      summary="Update existing category",
     *      description="Updates a category",
     *      security={{"BearerAuth":{}}},
     *      @OA\Parameter(
     *          name="uuid",
     *          description="Category uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     */
    public function update(Request $request, $uuid)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $uuid . ',uuid',
        ]);

        $category = Categories::where('uuid', $uuid)->firstOrFail();

        if ($request->has('title')) {
            $category->title = $request->title;
        }
        if ($request->has('slug')) {
            $category->slug = $request->slug;
        }

        $category->save();

        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/categories/{uuid}",
     *      operationId="deleteCategory",
     *      tags={"Categories"},
     *      summary="Delete existing category",
     *      description="Deletes a category",
     *      security={{"BearerAuth":{}}},
     *      @OA\Parameter(
     *          name="uuid",
     *          description="Category uuid",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       )
     * )
     */
    public function destroy($uuid)
    {
        $category = Categories::where('uuid', $uuid)->firstOrFail();
        $category->delete();

        return response()->json(null, 204);
    }
}

/**
 * @OA\Schema(
 *      schema="Categories",
 *      required={"uuid", "title", "slug"},
 *      @OA\Property(property="uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *      @OA\Property(property="title", type="string", example="Dog Food"),
 *      @OA\Property(property="slug", type="string", example="dog-food"),
 * )
 */

/**
 * @OA\Schema(
 *      schema="StoreCategoryRequest",
 *      required={"title", "slug"},
 *      @OA\Property(property="title", type="string", example="Dog Food"),
 *      @OA\Property(property="slug", type="string", example="dog-food"),
 * )
 */

/**
 * @OA\Schema(
 *      schema="UpdateCategoryRequest",
 *      required={"title", "slug"},
 *      @OA\Property(property="title", type="string", example="Dog Food"),
 *      @OA\Property(property="slug", type="string", example="dog-food"),
 * )
 */

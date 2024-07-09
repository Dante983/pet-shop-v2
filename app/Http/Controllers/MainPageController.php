<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class MainPageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/main/blog",
     *     operationId="listBlogs",
     *     tags={"Main"},
     *     summary="Get list of blog posts",
     *     description="Returns list of blog posts",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function listBlogs()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/main/blog/{uuid}",
     *     operationId="showBlog",
     *     tags={"Main"},
     *     summary="Get blog post information",
     *     description="Returns blog post data",
     *     @OA\Parameter(
     *         name="uuid",
     *         description="Blog post uuid",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog post not found"
     *     )
     * )
     */
    public function showBlog($uuid)
    {
        $post = Post::where('uuid', $uuid)->firstOrFail();
        return response()->json($post);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/main/promotions",
     *     operationId="listPromotions",
     *     tags={"Main"},
     *     summary="Get list of promotions",
     *     description="Returns list of promotions",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function listPromotions()
    {
        $promotions = Promotion::all();
        return response()->json($promotions);
    }
}

/**
 * @OA\Schema(
 *     schema="Post",
 *     required={"uuid", "title", "content"},
 *     @OA\Property(property="uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Sample Blog Post"),
 *     @OA\Property(property="content", type="string", example="This is the content of the blog post."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */

/**
 * @OA\Schema(
 *     schema="Promotion",
 *     required={"uuid", "title", "description"},
 *     @OA\Property(property="uuid", type="string", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="title", type="string", example="Sample Promotion"),
 *     @OA\Property(property="description", type="string", example="This is the description of the promotion."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 */

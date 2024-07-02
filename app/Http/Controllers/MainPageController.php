<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Promotion;
use Illuminate\Http\Request;

class MainPageController extends Controller
{
    /**
     * Display a listing of the blog posts.
     *
     * @return \Illuminate\Http\Response
     */
    public function listBlogs()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    /**
     * Display the specified blog post.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function showBlog($uuid)
    {
        $post = Post::where('uuid', $uuid)->firstOrFail();
        return response()->json($post);
    }

    /**
     * Display a listing of the promotions.
     *
     * @return \Illuminate\Http\Response
     */
    public function listPromotions()
    {
        $promotions = Promotion::all();
        return response()->json($promotions);
    }
}

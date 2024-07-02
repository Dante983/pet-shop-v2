<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
        ]);

        $category = new Category();
        $category->uuid = (string) Str::uuid();
        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $category = Category::where('uuid', $uuid)->firstOrFail();
        return response()->json($category);
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:categories,slug,' . $uuid . ',uuid',
        ]);

        $category = Category::where('uuid', $uuid)->firstOrFail();

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
     * Remove the specified category from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $category = Category::where('uuid', $uuid)->firstOrFail();
        $category->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Display a listing of the brands.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $brands = Brand::all();
        return response()->json($brands);
    }

    /**
     * Store a newly created brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
     * Display the specified brand.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $brand = Brand::where('uuid', $uuid)->firstOrFail();
        return response()->json($brand);
    }

    /**
     * Update the specified brand in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
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
     * Remove the specified brand from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $brand = Brand::where('uuid', $uuid)->firstOrFail();
        $brand->delete();

        return response()->json(null, 204);
    }
}

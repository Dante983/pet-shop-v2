<?php

use App\Handlers\Admin\AuthHandler;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\MainPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('create', [AdminController::class, 'create']);
        Route::post('login', [AdminController::class, 'login']);
        Route::middleware([AuthHandler::class])->group(function () {
            Route::get('logout', [AdminController::class, 'logout']);
            Route::get('user-listing', [AdminController::class, 'userListing']);
            Route::put('user-edit/{uuid}', [AdminController::class, 'userEdit']);
            Route::delete('user-delete/{uuid}', [AdminController::class, 'userDelete']);
        });
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::delete('/', [UserController::class, 'delete']);
        Route::post('create', [UserController::class, 'create']);
        Route::post('login', [UserController::class, 'login']);
        Route::get('logout', [UserController::class, 'logout']);
        Route::put('edit', [UserController::class, 'edit']);
    });

    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index']);
        Route::post('create', [BrandController::class, 'store']);
        Route::get('{uuid}', [BrandController::class, 'show']);
        Route::put('{uuid}', [BrandController::class, 'update']);
        Route::delete('{uuid}', [BrandController::class, 'destroy']);
    });

    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoriesController::class, 'index']);
        Route::post('create', [CategoriesController::class, 'store']);
        Route::get('{uuid}', [CategoriesController::class, 'show']);
        Route::put('{uuid}', [CategoriesController::class, 'update']);
        Route::delete('{uuid}', [CategoriesController::class, 'destroy']);
    });

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('create', [ProductController::class, 'store']);
        Route::get('{uuid}', [ProductController::class, 'show']);
        Route::put('{uuid}', [ProductController::class, 'update']);
        Route::delete('{uuid}', [ProductController::class, 'destroy']);
    });

    Route::prefix('main')->group(function () {
        Route::get('blog', [MainPageController::class, 'listBlogs']);
        Route::get('blog/{uuid}', [MainPageController::class, 'showBlog']);
        Route::get('promotions', [MainPageController::class, 'listPromotions']);
    });
});

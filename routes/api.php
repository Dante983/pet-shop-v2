<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('create', [AdminController::class, 'create']);
        Route::post('login', [AdminController::class, 'login']);
        Route::get('logout', [AdminController::class, 'logout']);
        Route::get('user-listing', [AdminController::class, 'userListing']);
        Route::put('user-edit/{uuid}', [AdminController::class, 'userEdit']);
        Route::delete('user-delete/{uuid}', [AdminController::class, 'userDelete']);
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
        Route::get('/', [CategoryController::class, 'index']);
        Route::post('create', [CategoryController::class, 'store']);
        Route::get('{uuid}', [CategoryController::class, 'show']);
        Route::put('{uuid}', [CategoryController::class, 'update']);
        Route::delete('{uuid}', [CategoryController::class, 'destroy']);
    });
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

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
        Route::post('create', [UserController::class, 'create']);
    });
});

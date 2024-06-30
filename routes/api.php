<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::prefix('v1')->group(function () {
    Route::prefix('admin')->group(function () {
        Route::post('create', [AdminController::class, 'create']);
    });
});

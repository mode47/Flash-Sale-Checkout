<?php

use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\HoldController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\WebHookController;

Route::prefix('v1')->group(function(){

    Route::prefix('products')->group(function(){
        Route::get('/{id}', [ProductController::class, 'show']);
    });
    Route::prefix('holds')->group(function(){
        Route::post('/', [HoldController::class, 'store']);
        Route::get('/{id}', [HoldController::class, 'show']);
    });
    Route::prefix('orders')->group(function(){
        Route::post('/', [OrderController::class, 'store']);
    });
    Route::post('/payments/webhook', [WebHookController::class, 'handle']);
});

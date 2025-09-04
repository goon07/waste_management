<?php

use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Define your API routes here
    Route::get('/test', function () {
        return response()->json(['message' => 'API is working']);
    });
});
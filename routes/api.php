<?php

use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::apiResource('students', StudentController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy'])
        ->middleware(['auth:sanctum', 'role:admin,staff']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\CustomerController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/visits', [VisitController::class, 'store']);


Route::get('/visits/hourly', [VisitController::class, 'hourly']);
Route::get('/customers', [CustomerController::class, 'index']);
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\InspeccionController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::apiResource('ingresos', IngresoController::class);
    Route::apiResource('inspecciones', InspeccionController::class);
});
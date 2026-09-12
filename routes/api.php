<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\InspeccionController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\DiagnosticoController;
use App\Http\Controllers\RepuestoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\ReparacionController;
use App\Http\Controllers\FotografiaController;
use App\Http\Controllers\SolicitudRepuestoController;
use App\Http\Controllers\PruebaController;
use App\Http\Controllers\InformeServicioController;



Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('vehiculos', VehiculoController::class);
    Route::apiResource('ingresos', IngresoController::class);
    Route::apiResource('inspecciones', InspeccionController::class);
    Route::apiResource('ordenes', OrdenController::class);
    Route::apiResource('diagnosticos', DiagnosticoController::class);
    Route::apiResource('repuestos', RepuestoController::class);
    Route::apiResource('cotizaciones', CotizacionController::class);
    Route::apiResource('asignaciones', AsignacionController::class);
    Route::apiResource('reparaciones', ReparacionController::class);
    Route::apiResource('fotografias', FotografiaController::class)->except(['update']);
    Route::apiResource('solicitudes-repuestos', SolicitudRepuestoController::class)->parameters(['solicitudes-repuestos' => 'solicitudesRepuesto']);
    Route::apiResource('pruebas', PruebaController::class)->parameters(['pruebas' => 'prueba']);
    Route::apiResource('informes-servicio', InformeServicioController::class)->parameters(['informes-servicio' => 'informesServicio'])->except(['update']);
    Route::get('informes-servicio/{informesServicio}/pdf', [InformeServicioController::class, 'pdf']);
});
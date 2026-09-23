<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Nuestro controlador de API para el login
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrabajoController;

// Controladores Base
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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Nuestra ruta para que el celular inicie sesión
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Nuestra nueva ruta para la app móvil
    Route::get('/mis-trabajos', [TrabajoController::class, 'misTrabajos']); // <-- AGREGAR ESTA LÍNEA

    // Administrador y Jefe de taller: acceso total
    Route::middleware('role:Administrador,Jefe de taller')->group(function () {
        Route::apiResource('clientes', ClienteController::class);
        Route::apiResource('vehiculos', VehiculoController::class);
        Route::apiResource('ingresos', IngresoController::class);
        Route::apiResource('inspecciones', InspeccionController::class);
        Route::apiResource('ordenes', OrdenController::class);
        Route::apiResource('repuestos', RepuestoController::class);
        Route::apiResource('cotizaciones', CotizacionController::class);
        Route::apiResource('asignaciones', AsignacionController::class);
        Route::apiResource('informes-servicio', InformeServicioController::class)
            ->parameters(['informes-servicio' => 'informesServicio'])->except(['update']);
        Route::get('informes-servicio/{informesServicio}/pdf', [InformeServicioController::class, 'pdf']);
        Route::apiResource('solicitudes-repuestos', SolicitudRepuestoController::class)
            ->parameters(['solicitudes-repuestos' => 'solicitudesRepuesto']);
    });

    // Contabilidad: solo lectura
    Route::middleware('role:Contabilidad,Administrador,Jefe de taller')->group(function () {
        Route::get('clientes', [ClienteController::class, 'index']);
        Route::get('vehiculos', [VehiculoController::class, 'index']);
        Route::get('ordenes', [OrdenController::class, 'index']);
        Route::get('ordenes/{orden}', [OrdenController::class, 'show']);
        Route::get('cotizaciones', [CotizacionController::class, 'index']);
        Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show']);
        Route::get('informes-servicio', [InformeServicioController::class, 'index']);
        Route::get('informes-servicio/{informesServicio}', [InformeServicioController::class, 'show']);
    });

    // Mecanico: diagnosticos, reparaciones, fotos, pruebas, solicitar repuestos
    Route::middleware('role:Mecánico,Administrador,Jefe de taller')->group(function () {
        Route::apiResource('diagnosticos', DiagnosticoController::class)->except(['destroy']);
        Route::apiResource('reparaciones', ReparacionController::class)->except(['destroy']);
        Route::apiResource('fotografias', FotografiaController::class)->except(['update']);
        Route::apiResource('pruebas', PruebaController::class)->except(['destroy']);
        Route::post('solicitudes-repuestos', [SolicitudRepuestoController::class, 'store']);
        Route::get('solicitudes-repuestos', [SolicitudRepuestoController::class, 'index']);
        Route::get('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'show']);
        Route::get('ordenes', [OrdenController::class, 'index']);
        Route::get('ordenes/{orden}', [OrdenController::class, 'show']);
        Route::get('repuestos', [RepuestoController::class, 'index']);
    });

    // Mensajero: ver y actualizar solicitudes de repuestos
    Route::middleware('role:Mensajero,Administrador,Jefe de taller')->group(function () {
        Route::get('solicitudes-repuestos', [SolicitudRepuestoController::class, 'index']);
        Route::get('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'show']);
        Route::put('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'update']);
    });
});
});
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TrabajoController;
use App\Http\Controllers\Api\FotoIngresoController;
use App\Http\Controllers\Api\NotificacionController;

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

/*
 * IMPORTANTE: Laravel usa la PRIMERA ruta que coincide con la URL y el método, sin importar
 * el rol. Por eso las rutas que varios roles comparten van en los grupos de arriba (más
 * amplios) y las exclusivas de Administrador / Jefe de taller van al final.
 */

// Login de la app móvil (máx. 10 intentos por minuto)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', fn (\Illuminate\Http\Request $request) => $request->user());

    // Notificaciones del usuario que inició sesión (cualquier rol)
    Route::get('/notificaciones', [NotificacionController::class, 'index']);
    Route::get('/notificaciones/contador', [NotificacionController::class, 'contador']);
    Route::post('/notificaciones/leer', [NotificacionController::class, 'leerTodas']);

    // Trabajos asignados al mecánico que inició sesión
    Route::get('/mis-trabajos', [TrabajoController::class, 'misTrabajos']);
    // El mecánico marca su orden "en reparación" o "finalizada" (y el vehículo se actualiza solo)
    Route::put('/mis-trabajos/{orden}/estado', [TrabajoController::class, 'cambiarEstado']);

    // 1) Consulta de vehículos, órdenes y fotos: todo el personal del taller
    Route::middleware('role:Administrador,Jefe de taller,Contabilidad,Mecánico')->group(function () {
        Route::get('ingresos', [IngresoController::class, 'index']);
        Route::get('ingresos/{ingreso}', [IngresoController::class, 'show']);
        Route::get('ingresos/{ingreso}/fotos', [FotoIngresoController::class, 'index']);
        Route::get('ordenes', [OrdenController::class, 'index']);
        Route::get('ordenes/{orden}', [OrdenController::class, 'show']);
    });

    // 2) Contabilidad (secretaria): consulta y cambio de estado del vehículo
    Route::middleware('role:Administrador,Jefe de taller,Contabilidad')->group(function () {
        Route::get('clientes', [ClienteController::class, 'index']);
        Route::get('vehiculos', [VehiculoController::class, 'index']);
        Route::get('cotizaciones', [CotizacionController::class, 'index']);
        Route::get('cotizaciones/{cotizacion}', [CotizacionController::class, 'show']);
        Route::get('informes-servicio', [InformeServicioController::class, 'index']);
        Route::get('informes-servicio/{informesServicio}', [InformeServicioController::class, 'show']);
        Route::get('informes-servicio/{informesServicio}/pdf', [InformeServicioController::class, 'pdf']);
        // Cambiar el estado del vehículo: Administrador, Jefe de taller y Contabilidad
        Route::put('ingresos/{ingreso}/estado', [IngresoController::class, 'cambiarEstado']);
        // Generar informes: solo Administrador, Jefe de taller y Contabilidad (el mecánico no)
        Route::post('informes-servicio', [InformeServicioController::class, 'store']);
    });

    // 3) Trabajo en el taller: Mecánico, Jefe de taller y Administrador
    //    (no hay rutas para borrar: diagnósticos, reparaciones, pruebas y fotos son evidencia)
    Route::middleware('role:Mecánico,Administrador,Jefe de taller')->group(function () {
        Route::post('ingresos/{ingreso}/fotos', [FotoIngresoController::class, 'store']);
        Route::apiResource('diagnosticos', DiagnosticoController::class)->except(['destroy']);
        Route::apiResource('reparaciones', ReparacionController::class)->except(['destroy']);
        Route::apiResource('fotografias', FotografiaController::class)->except(['update', 'destroy']);
        Route::apiResource('pruebas', PruebaController::class)->except(['destroy']);
        Route::get('repuestos', [RepuestoController::class, 'index']);
        Route::get('repuestos/{repuesto}', [RepuestoController::class, 'show']);
        Route::post('solicitudes-repuestos', [SolicitudRepuestoController::class, 'store']);
    });

    // 4) Solicitudes de repuestos: el mecánico las crea y las ve, el mensajero las atiende
    Route::middleware('role:Mensajero,Mecánico,Administrador,Jefe de taller')->group(function () {
        Route::get('solicitudes-repuestos', [SolicitudRepuestoController::class, 'index']);
        Route::get('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'show']);
    });
    Route::middleware('role:Mensajero,Administrador,Jefe de taller')->group(function () {
        Route::put('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'update']);
    });

    // 5) Solo Administrador y Jefe de taller (lo que no está arriba)
    Route::middleware('role:Administrador,Jefe de taller')->group(function () {
        Route::apiResource('clientes', ClienteController::class)->except(['index']);
        Route::apiResource('vehiculos', VehiculoController::class)->except(['index']);
        Route::apiResource('ingresos', IngresoController::class)->except(['index', 'show']);
        Route::apiResource('inspecciones', InspeccionController::class)->except(['destroy']);
        Route::apiResource('repuestos', RepuestoController::class)->except(['index', 'show']);
        Route::apiResource('asignaciones', AsignacionController::class);
        Route::delete('informes-servicio/{informesServicio}', [InformeServicioController::class, 'destroy']);
        Route::delete('solicitudes-repuestos/{solicitudesRepuesto}', [SolicitudRepuestoController::class, 'destroy']);

        // Asignar trabajos desde el celular
        Route::get('taller/ordenes', [TrabajoController::class, 'ordenesAsignables']);
        Route::get('taller/mecanicos', [TrabajoController::class, 'mecanicos']);
        Route::put('taller/ordenes/{orden}/mecanico', [TrabajoController::class, 'asignarMecanico']);
    });
});

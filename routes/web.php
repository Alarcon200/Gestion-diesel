<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Clientes;
use App\Livewire\Vehiculos;
use App\Livewire\Ordenes;
use App\Livewire\Ingresos;
use App\Livewire\Diagnosticos;
use App\Livewire\Cotizaciones; 
use App\Models\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Livewire\Dashboard;
use App\Livewire\Panel;
use App\Livewire\Usuarios;
use App\Livewire\Asignaciones;

Route::get('/cotizaciones/{cotizacion}/pdf', function (\App\Models\Cotizacion $cotizacion) {
    if (!session('usuario_id')) return redirect('/login');

    $cotizacion->load('detalles', 'orden.vehiculo.cliente', 'vehiculo.cliente');

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.cotizacion', ['cotizacion' => $cotizacion]);

    return $pdf->download('cotizacion-' . ($cotizacion->orden->numero_orden ?? 'libre-' . $cotizacion->id) . '.pdf');
});

Route::get('/', Login::class);
Route::get('/login', Login::class)->name('login');

Route::get('/logout', function () {
    session()->flush();
    return redirect('/login');
});

Route::get('/panel', function () {
    if (!session('usuario_id')) return redirect('/login');
    return view('panel');
});
Route::get('/vehiculos', Vehiculos::class);
Route::get('/clientes', Clientes::class);
Route::get('/ordenes', Ordenes::class);
Route::get('/ingresos', Ingresos::class);
Route::get('/diagnosticos', Diagnosticos::class);
Route::get('/cotizaciones', Cotizaciones::class);
Route::get('/dashboard', Dashboard::class);
Route::get('/panel', Panel::class);
Route::get('/usuarios', Usuarios::class);
Route::get('/asignaciones', Asignaciones::class);
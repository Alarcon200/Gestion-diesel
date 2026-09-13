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

Route::get('/cotizaciones/{cotizacion}/pdf', function (Cotizacion $cotizacion) {
    if (!session('usuario_id')) return redirect('/login');

    $cotizacion->load('detalles', 'orden.vehiculo.cliente');

    $pdf = Pdf::loadView('pdf.cotizacion', ['cotizacion' => $cotizacion]);

    return $pdf->download('cotizacion-' . $cotizacion->orden->numero_orden . '.pdf');
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
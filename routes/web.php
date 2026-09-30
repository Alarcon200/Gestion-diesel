<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Clientes;
use App\Livewire\Vehiculos;
use App\Livewire\Ingresos;
use App\Livewire\Diagnosticos;
use App\Livewire\Dashboard;
use App\Livewire\Panel;
use App\Livewire\Usuarios;
use App\Livewire\Asignaciones;



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

Route::get('/informes/{informe}/pdf', function (\App\Models\InformeServicio $informe) {
    if (!session('usuario_id')) return redirect('/login');

    $orden = $informe->orden()->with('vehiculo.cliente', 'diagnosticos', 'reparaciones.fotografias', 'reparaciones.mecanico', 'pruebas')->first();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.informe', ['informe' => $informe, 'orden' => $orden]);

    return $pdf->download('informe-' . $orden->numero_orden . '.pdf');
});

Route::get('/vehiculos', Vehiculos::class);
Route::get('/clientes', Clientes::class);
Route::get('/ingresos', Ingresos::class);
Route::get('/diagnosticos', Diagnosticos::class);
Route::get('/dashboard', Dashboard::class);
Route::get('/panel', Panel::class);
Route::get('/usuarios', Usuarios::class);
Route::get('/asignaciones', Asignaciones::class);
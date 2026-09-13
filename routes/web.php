<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Login;
use App\Livewire\Clientes;
use App\Livewire\Vehiculos;
use App\Livewire\Ordenes;
use App\Livewire\Ingresos;


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
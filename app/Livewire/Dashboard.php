<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cotizacion;
use App\Models\Orden;
use App\Models\Ingreso; 
use Carbon\Carbon;

class Dashboard extends Component
{
    public function mount()
    {
        // Protegemos la ruta para que solo entren usuarios logueados
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function render()
    {
        // Definimos el rango de la semana actual
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();

        // 1. Tarjetas rápidas (Estado actual)
        // (Asumimos que las órdenes tienen un campo 'estado', puedes ajustarlo según tu base de datos)
        $ordenesEnProceso = Orden::whereNotIn('estado', ['finalizada', 'entregada'])->count(); 
        
        $cotizacionesPendientes = Cotizacion::where('estado', 'pendiente')->count();
        
        // 2. Resumen Semanal
        $ingresosSemana = Orden::whereBetween('fecha_creacion', [$inicioSemana, $finSemana])->count();

        // Suma del dinero en cotizaciones aprobadas esta semana
        $dineroAprobadoSemana = Cotizacion::where('estado', 'autorizada')
            ->whereBetween('fecha_respuesta', [$inicioSemana, $finSemana])
            ->sum('total');

        // 3. Últimas cotizaciones pendientes para acción rápida
        $ultimasCotizaciones = Cotizacion::with('orden.vehiculo')
            ->where('estado', 'pendiente')
            ->orderBy('fecha_creacion', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard',[
            'ultimasCotizaciones' => $ultimasCotizaciones,
        ])->layout('layouts.app');
    }
} 
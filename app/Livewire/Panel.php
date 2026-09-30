<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;

class Panel extends Component
{
    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function render()
    {
        $enTaller = Ingreso::whereNull('fecha_salida')->get();

        $conteos = [
            'en_espera_diagnostico' => $enTaller->where('estado', 'en_espera_diagnostico')->count(),
            'en_reparacion' => $enTaller->where('estado', 'en_reparacion')->count(),
            'pendiente_cotizacion' => $enTaller->where('estado', 'pendiente_cotizacion')->count(),
            'pendiente_informe' => $enTaller->where('estado', 'pendiente_informe')->count(),
            'pendiente_prefactura' => $enTaller->where('estado', 'pendiente_prefactura')->count(),
            'orden_enviada' => $enTaller->where('estado', 'orden_enviada')->count(),
        ];

        return view('livewire.panel', [
            'totalEnTaller' => $enTaller->count(),
            'conteos' => $conteos,
            'salidosHoy' => Ingreso::whereDate('fecha_salida', now()->toDateString())->count(),
            'ultimosIngresos' => Ingreso::with('vehiculo.cliente')
                ->whereNull('fecha_salida')
                ->orderByDesc('fecha_ingreso')
                ->limit(5)
                ->get(),
        ])->layout('layouts.app');
    }
}
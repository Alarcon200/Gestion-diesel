<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;
use App\Models\Orden;
use Carbon\Carbon;

class Dashboard extends Component
{
    // Etapas del flujo del taller (en el orden en que avanza un vehículo)
    protected function etapas(): array
    {
        return [
            'en_espera_diagnostico' => ['label' => 'En espera de diagnóstico', 'barra' => 'bg-slate-400', 'chip' => 'bg-slate-100 text-slate-700 border-slate-300'],
            'en_reparacion' => ['label' => 'En reparación', 'barra' => 'bg-isa-blue', 'chip' => 'bg-blue-50 text-blue-800 border-blue-200'],
            'pendiente_cotizacion' => ['label' => 'Pendiente de cotización', 'barra' => 'bg-amber-500', 'chip' => 'bg-amber-50 text-amber-800 border-amber-200'],
            'pendiente_informe' => ['label' => 'Pendiente de informe', 'barra' => 'bg-violet-500', 'chip' => 'bg-violet-50 text-violet-800 border-violet-200'],
            'pendiente_prefactura' => ['label' => 'Pendiente de prefactura', 'barra' => 'bg-pink-500', 'chip' => 'bg-pink-50 text-pink-800 border-pink-200'],
            'orden_enviada' => ['label' => 'Orden enviada', 'barra' => 'bg-indigo-500', 'chip' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
        ];
    }

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }

        $rol = mb_strtolower(trim((string) session('usuario_rol')));
        if (!in_array($rol, ['administrador', 'jefe de taller', 'contabilidad'])) {
            return redirect('/panel');
        }
    }

    public function render()
    {
        $hoy = Carbon::now();
        $inicioSemana = $hoy->copy()->startOfWeek();
        $finSemana = $hoy->copy()->endOfWeek();
        $etapas = $this->etapas();

        // Vehículos que siguen dentro del taller
        $enTaller = Ingreso::with('vehiculo.cliente', 'ordenes')
            ->withCount('inspecciones')
            ->where('estado', '!=', 'salio_taller')
            ->orderByDesc('fecha_ingreso')
            ->get();

        $tieneST = fn ($ing) => $ing->ordenes->contains(fn ($o) => str_starts_with((string) $o->numero_orden, 'ST'));

        // Días que lleva cada vehículo dentro del taller
        $dias = [];
        foreach ($enTaller as $ing) {
            $entrada = Carbon::parse($ing->fecha_ingreso)->startOfDay();
            $dias[$ing->id] = max(0, (int) $entrada->diffInDays($hoy->copy()->startOfDay()));
        }

        $totalSinST = $enTaller->filter(fn ($i) => !$tieneST($i))->count();
        $totalSinInspeccion = $enTaller->filter(fn ($i) => $i->inspecciones_count === 0)->count();
        $ingresosSemana = Ingreso::whereBetween('fecha_ingreso', [$inicioSemana, $finSemana])->count();

        // Lo que necesita atención: sin ST, sin inspección o muchos días en el taller
        $atencion = [];
        foreach ($enTaller as $ing) {
            $razones = [];
            if (!$tieneST($ing)) {
                $razones[] = ['texto' => 'Sin número ST', 'clase' => 'bg-amber-100 text-amber-900 border-amber-300'];
            }
            if ($ing->inspecciones_count === 0) {
                $razones[] = ['texto' => 'Sin inspección', 'clase' => 'bg-red-100 text-red-800 border-red-300'];
            }
            if ($dias[$ing->id] >= 7) {
                $razones[] = ['texto' => $dias[$ing->id] . ' días en el taller', 'clase' => 'bg-slate-200 text-slate-800 border-slate-400'];
            }
            if ($razones) {
                $atencion[] = ['ingreso' => $ing, 'razones' => $razones];
            }
        }

        // Cuántos vehículos hay en cada etapa
        $conteos = [];
        foreach (array_keys($etapas) as $clave) {
            $conteos[$clave] = $enTaller->where('estado', $clave)->count();
        }

        // Ingresos de los últimos 7 días
        $fechas = Ingreso::where('fecha_ingreso', '>=', $hoy->copy()->subDays(6)->startOfDay())->pluck('fecha_ingreso');
        $ultimosDias = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = $hoy->copy()->subDays($i)->startOfDay();
            $ultimosDias[] = [
                'dia' => $dia->copy()->locale('es')->isoFormat('dd'),
                'total' => $fechas->filter(fn ($f) => Carbon::parse($f)->isSameDay($dia))->count(),
                'hoy' => $i === 0,
            ];
        }
        $maxDia = max(1, max(array_column($ultimosDias, 'total')));

        // Trabajo de cada mecánico
        $ordenesAbiertas = Orden::with('mecanico')
            ->whereNotIn('estado', ['finalizada', 'entregada', 'cancelada'])
            ->whereHas('ingreso', fn ($q) => $q->where('estado', '!=', 'salio_taller'))
            ->get();

        $carga = [];
        foreach ($ordenesAbiertas->whereNotNull('mecanico_id')->groupBy('mecanico_id') as $grupo) {
            $m = $grupo->first()->mecanico;
            $carga[] = [
                'nombre' => $m ? trim($m->nombre . ' ' . ($m->apellido ?? '')) : 'Mecánico',
                'total' => $grupo->count(),
                'reparando' => $grupo->where('estado', 'en_reparacion')->count(),
            ];
        }
        usort($carga, fn ($a, $b) => $b['total'] <=> $a['total']);
        $sinMecanico = $ordenesAbiertas->whereNull('mecanico_id')->count();

        return view('livewire.dashboard', [
            'hoy' => $hoy,
            'etapas' => $etapas,
            'enTaller' => $enTaller,
            'dias' => $dias,
            'totalEnTaller' => $enTaller->count(),
            'totalSinST' => $totalSinST,
            'totalSinInspeccion' => $totalSinInspeccion,
            'ingresosSemana' => $ingresosSemana,
            'atencion' => $atencion,
            'conteos' => $conteos,
            'ultimosDias' => $ultimosDias,
            'maxDia' => $maxDia,
            'carga' => $carga,
            'sinMecanico' => $sinMecanico,
        ])->layout('layouts.app');
    }
}

<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;
use App\Models\Orden;
use App\Models\Diagnostico;
use Illuminate\Validation\ValidationException;

class Panel extends Component
{
    public $orden_id = '';
    public $problema_encontrado = '';
    public $comentario = '';
    public $mensaje = '';

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function actualizarEstado($ordenId, $estado)
    {
        if (session('usuario_rol') !== 'Mecánico') {
            abort(403);
        }

        $estadosPermitidos = [
            'pendiente',
            'en_reparacion',
            'finalizada',
        ];

        if (!in_array($estado, $estadosPermitidos, true)) {
            abort(422, 'Estado no permitido.');
        }

        // El mecánico solo puede actualizar sus propias órdenes.
        $orden = Orden::where('id', $ordenId)
            ->where('mecanico_id', session('usuario_id'))
            ->firstOrFail();

        $orden->estado = $estado;

        if ($estado === 'finalizada') {
            $orden->fecha_finalizacion = now();
        }

        $orden->save();

        $this->mensaje = 'El estado de la orden se actualizó correctamente.';
    }

    public function reportarFalla()
    {
        if (session('usuario_rol') !== 'Mecánico') {
            abort(403);
        }

        $this->validate([
            'orden_id' => 'required|integer|exists:ordenes,id',
            'problema_encontrado' => 'required|string|min:5|max:5000',
            'comentario' => 'nullable|string|max:5000',
        ]);

        $orden = Orden::where('id', $this->orden_id)
            ->where('mecanico_id', session('usuario_id'))
            ->first();

        if (!$orden) {
            throw ValidationException::withMessages([
                'orden_id' => 'Solo puedes reportar fallas de una orden asignada a ti.',
            ]);
        }

        Diagnostico::create([
            'orden_id' => $orden->id,
            'usuario_id' => session('usuario_id'),
            'problema_encontrado' => $this->problema_encontrado,
            'causa_probable' => null,
            'trabajo_a_realizar' => 'Reporte enviado al jefe de taller para evaluación.',
            'recomendaciones' => $this->comentario ?: null,
            'fecha' => now(),
        ]);

        $this->reset(['orden_id', 'problema_encontrado', 'comentario']);
        $this->mensaje = 'La falla fue reportada al jefe de taller.';
    }

    public function render()
    {
        $esMecanico = session('usuario_rol') === 'Mecánico';

        if ($esMecanico) {
            $mecanicoId = session('usuario_id');

            // Órdenes que el jefe de taller aún no ha asignado.
            $ordenesDisponibles = Orden::with('vehiculo')
                ->whereNull('mecanico_id')
                ->whereNotIn('estado', ['finalizada', 'entregada', 'cancelada'])
                ->orderByDesc('fecha_creacion')
                ->get();

            // Solo las órdenes asignadas al mecánico conectado.
            // Se necesitan para permitir actualizar su estado y reportar fallas.
            $misOrdenes = Orden::with('vehiculo')
                ->where('mecanico_id', $mecanicoId)
                ->orderByDesc('fecha_creacion')
                ->get();

            $misReparaciones = Orden::where('mecanico_id', $mecanicoId)
                ->whereIn('estado', ['finalizada', 'entregada'])
                ->count();

            $misVehiculosSalidos = Orden::where('mecanico_id', $mecanicoId)
                ->where('estado', 'entregada')
                ->count();

            $misOrdenesActivas = Orden::where('mecanico_id', $mecanicoId)
                ->whereNotIn('estado', ['finalizada', 'entregada', 'cancelada'])
                ->count();

            return view('livewire.panel', compact(
                'ordenesDisponibles',
                'misOrdenes',
                'misReparaciones',
                'misVehiculosSalidos',
                'misOrdenesActivas'
            ))->layout('layouts.app');
        }

        $enTaller = Ingreso::whereNotIn('estado', [
            'entregado', 'entregada', 'salido', 'finalizado', 'finalizada'
        ])->get();

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
            'salidosHoy' => 0,
            'ultimosIngresos' => Ingreso::with('vehiculo.cliente')
                ->whereNotIn('estado', [
                    'entregado', 'entregada', 'salido', 'finalizado', 'finalizada'
                ])
                ->orderByDesc('fecha_ingreso')
                ->limit(5)
                ->get(),
        ])->layout('layouts.app');
    }
}


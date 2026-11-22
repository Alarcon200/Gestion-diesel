<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;
use App\Models\Orden;
use App\Models\Diagnostico;
use App\Models\HistorialEstadoIngreso;
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

        // Actualiza la orden y también el estado del vehículo en la tabla del taller
        $orden->cambiarEstadoPorMecanico($estado, (int) session('usuario_id'));

        $this->mensaje = 'El estado de la orden se actualizó correctamente.';
    }

    // Etapas que se muestran en el panel (el orden es el del flujo del taller)
    protected function etapas(): array
    {
        return [
            'en_espera_diagnostico' => ['label' => 'Espera de diagnóstico', 'borde' => 'border-slate-400', 'chip' => 'bg-slate-100 text-slate-700 border-slate-300'],
            'en_reparacion' => ['label' => 'En reparación', 'borde' => 'border-isa-blue', 'chip' => 'bg-blue-50 text-blue-800 border-blue-200'],
            'pendiente_cotizacion' => ['label' => 'Pendiente de cotización', 'borde' => 'border-amber-500', 'chip' => 'bg-amber-50 text-amber-800 border-amber-200'],
            'pendiente_informe' => ['label' => 'Pendiente de informe', 'borde' => 'border-violet-500', 'chip' => 'bg-violet-50 text-violet-800 border-violet-200'],
            'pendiente_prefactura' => ['label' => 'Pendiente de prefactura', 'borde' => 'border-pink-500', 'chip' => 'bg-pink-50 text-pink-800 border-pink-200'],
            'orden_enviada' => ['label' => 'Orden enviada', 'borde' => 'border-indigo-500', 'chip' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
        ];
    }

    protected function todosLosEstados(): array
    {
        return $this->etapas() + [
            'salio_taller' => ['label' => 'Salió del taller', 'borde' => 'border-green-600', 'chip' => 'bg-green-50 text-green-800 border-green-200'],
        ];
    }

    // Administrador, Jefe de taller y Contabilidad (secretaria) pueden cambiar el estado
    protected function puedeCambiarEstado(): bool
    {
        $rol = mb_strtolower(trim((string) session('usuario_rol')));
        return in_array($rol, ['administrador', 'jefe de taller', 'contabilidad']);
    }

    public function cambiarEstado($ingresoId, $nuevoEstado)
    {
        if (!$this->puedeCambiarEstado()) {
            abort(403);
        }

        if (!array_key_exists($nuevoEstado, $this->todosLosEstados())) {
            return;
        }

        $ingreso = Ingreso::find($ingresoId);
        if (!$ingreso || $ingreso->estado === $nuevoEstado) {
            return;
        }

        $estadoAnterior = $ingreso->estado;

        $ingreso->update([
            'estado' => $nuevoEstado,
            'fecha_salida' => $nuevoEstado === 'salio_taller' ? now() : null,
        ]);

        HistorialEstadoIngreso::create([
            'ingreso_id' => $ingreso->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => session('usuario_id'),
            'fecha' => now(),
        ]);

        $this->mensaje = 'Estado actualizado: ' . $this->todosLosEstados()[$nuevoEstado]['label'] . '.';
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

        // Vehículos que siguen dentro del taller (los que ya salieron no cuentan)
        $enTaller = Ingreso::with('vehiculo.cliente', 'ordenes')
            ->whereNotIn('estado', [
                'salio_taller', 'entregado', 'entregada', 'salido', 'finalizado', 'finalizada',
            ])
            ->orderByDesc('fecha_ingreso')
            ->get();

        $etapas = $this->etapas();
        $conteos = [];
        foreach (array_keys($etapas) as $clave) {
            $conteos[$clave] = $enTaller->where('estado', $clave)->count();
        }

        return view('livewire.panel-taller', [
            'totalEnTaller' => $enTaller->count(),
            'etapas' => $etapas,
            'estados' => $this->todosLosEstados(),
            'conteos' => $conteos,
            'ingresos' => $enTaller,
            'puedeCambiar' => $this->puedeCambiarEstado(),
        ])->layout('layouts.app');
    }
}


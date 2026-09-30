<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;
use App\Models\Vehiculo;
use App\Models\Orden;
use App\Models\HistorialEstadoIngreso;
use App\Models\InformeServicio;

class Ingresos extends Component
{
    public $vehiculo_id = '';
    public $conductor = '';
    public $fecha_ingreso = '';
    public $motivo = '';
    public $kilometraje = '';
    public $mostrar_formulario = false;

    public $filtro_placa = '';
    public $filtro_orden = '';
    public $filtro_cliente = '';
    public $filtro_fecha_desde = '';
    public $filtro_fecha_hasta = '';

    public $asignando_orden_id = null;
    public $numero_orden_sufijo = '';

    public $viendo_historial_id = null;

    public $estados = [
        'en_espera_diagnostico' => ['label' => 'En espera de ser diagnosticado', 'color' => 'bg-gray-100 text-gray-700'],
        'en_reparacion' => ['label' => 'En proceso de reparacion', 'color' => 'bg-blue-100 text-blue-700'],
        'pendiente_cotizacion' => ['label' => 'Pendiente cotizacion', 'color' => 'bg-amber-100 text-amber-700'],
        'pendiente_informe' => ['label' => 'Pendiente informe', 'color' => 'bg-purple-100 text-purple-700'],
        'pendiente_prefactura' => ['label' => 'Pendiente prefactura', 'color' => 'bg-pink-100 text-pink-700'],
        'orden_enviada' => ['label' => 'Orden enviada', 'color' => 'bg-indigo-100 text-indigo-700'],
        'salio_taller' => ['label' => 'Salio del taller', 'color' => 'bg-green-100 text-green-700'],
    ];

    protected $rules = [
        'vehiculo_id' => 'required|exists:vehiculos,id',
        'conductor' => 'nullable|string|max:120',
        'fecha_ingreso' => 'required|date',
        'motivo' => 'required|string|max:200',
        'kilometraje' => 'nullable|integer',
    ];

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
        $this->fecha_ingreso = now()->format('Y-m-d\TH:i');
    }

    public function nuevo()
    {
        $this->resetForm();
        $this->fecha_ingreso = now()->format('Y-m-d\TH:i');
        $this->mostrar_formulario = true;
    }

    public function guardar()
    {
        $this->validate();

        $ingreso = Ingreso::create([
            'vehiculo_id' => $this->vehiculo_id,
            'usuario_id' => session('usuario_id'),
            'conductor' => $this->conductor,
            'fecha_ingreso' => $this->fecha_ingreso,
            'motivo' => $this->motivo,
            'kilometraje' => $this->kilometraje ?: null,
            'estado' => 'en_espera_diagnostico',
        ]);

        HistorialEstadoIngreso::create([
            'ingreso_id' => $ingreso->id,
            'estado_anterior' => null,
            'estado_nuevo' => 'en_espera_diagnostico',
            'usuario_id' => session('usuario_id'),
            'fecha' => now(),
        ]);

        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    public function cancelar()
    {
        $this->resetForm();
        $this->mostrar_formulario = false;
    }

    public function limpiarFiltros()
    {
        $this->reset(['filtro_placa', 'filtro_orden', 'filtro_cliente', 'filtro_fecha_desde', 'filtro_fecha_hasta']);
    }

    public function cambiarEstado($ingresoId, $nuevoEstado)
    {
        if (!array_key_exists($nuevoEstado, $this->estados)) {
            return;
        }

        $ingreso = Ingreso::find($ingresoId);
        if (!$ingreso) {
            return;
        }

        $estadoAnterior = $ingreso->estado;

        $data = ['estado' => $nuevoEstado];
        $data['fecha_salida'] = $nuevoEstado === 'salio_taller' ? now() : null;

        $ingreso->update($data);

        HistorialEstadoIngreso::create([
            'ingreso_id' => $ingreso->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => session('usuario_id'),
            'fecha' => now(),
        ]);

        session()->flash('mensaje_estado', 'Estado actualizado a: ' . $this->estados[$nuevoEstado]['label']);
    }

    public function mostrarAsignarOrden($ingresoId)
    {
        $this->asignando_orden_id = $ingresoId;
        $this->numero_orden_sufijo = '';
    }

    public function cancelarAsignarOrden()
    {
        $this->asignando_orden_id = null;
        $this->numero_orden_sufijo = '';
    }

    public function guardarNumeroOrden()
    {
        $this->validate([
            'numero_orden_sufijo' => 'required|string|max:20',
        ]);

        $numeroCompleto = 'ST' . $this->numero_orden_sufijo;

        $existe = Orden::where('numero_orden', $numeroCompleto)->exists();
        if ($existe) {
            $this->addError('numero_orden_sufijo', 'Ese numero de orden ya esta en uso.');
            return;
        }

        $orden = Orden::obtenerOCrearParaIngreso($this->asignando_orden_id, session('usuario_id'));
        $orden->update(['numero_orden' => $numeroCompleto]);

        $this->asignando_orden_id = null;
        $this->numero_orden_sufijo = '';
    }

    public function verHistorial($ingresoId)
    {
        $this->viendo_historial_id = $this->viendo_historial_id === $ingresoId ? null : $ingresoId;
    }

    public function generarInforme($ingresoId)
    {
        $orden = Orden::obtenerOCrearParaIngreso($ingresoId, session('usuario_id'));

        InformeServicio::create([
            'orden_id' => $orden->id,
            'usuario_id' => session('usuario_id'),
            'valor_servicio' => 0,
            'observaciones_finales' => null,
            'fecha_generacion' => now(),
        ]);

        session()->flash('mensaje_estado', 'Informe generado correctamente.');
    }

    private function resetForm()
    {
        $this->reset(['vehiculo_id', 'conductor', 'fecha_ingreso', 'motivo', 'kilometraje']);
        $this->resetValidation();
    }

    public function render()
    {
        $query = Ingreso::with('vehiculo.cliente', 'usuario', 'ordenes.informes', 'historial.usuario')
            ->orderByDesc('fecha_ingreso');

        if ($this->filtro_placa) {
            $query->whereHas('vehiculo', fn($q) => $q->where('placa', 'ilike', '%' . $this->filtro_placa . '%'));
        }

        if ($this->filtro_cliente) {
            $query->whereHas('vehiculo.cliente', fn($q) => $q->where('nombre', 'ilike', '%' . $this->filtro_cliente . '%'));
        }

        if ($this->filtro_orden) {
            $query->whereHas('ordenes', fn($q) => $q->where('numero_orden', 'ilike', '%' . $this->filtro_orden . '%'));
        }

        if ($this->filtro_fecha_desde) {
            $query->whereDate('fecha_ingreso', '>=', $this->filtro_fecha_desde);
        }

        if ($this->filtro_fecha_hasta) {
            $query->whereDate('fecha_ingreso', '<=', $this->filtro_fecha_hasta);
        }

        return view('livewire.ingresos', [
            'ingresos' => $query->get(),
            'vehiculos' => Vehiculo::with('cliente')->orderBy('placa')->get(),
        ])->layout('layouts.app');
    }
}
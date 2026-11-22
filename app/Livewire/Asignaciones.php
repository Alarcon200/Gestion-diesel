<?php

namespace App\Livewire;

use App\Models\Ingreso;
use App\Models\Orden;
use App\Models\Usuario;
use Livewire\Component;

class Asignaciones extends Component
{
    public $orden_id = '';
    public $mecanico_id = '';
    public $mostrar_formulario = false;

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }

        if (!in_array(session('usuario_rol'), [
            'Administrador',
            'Jefe de taller',
            'Jefe de Taller'
        ])) {
            abort(403);
        }
    }

    public function render()
    {
        // Todo vehículo que sigue en el taller debe tener su orden para poder asignarlo
        Ingreso::where('estado', '!=', 'salio_taller')
            ->doesntHave('ordenes')
            ->get()
            ->each(fn ($ingreso) => Orden::obtenerOCrearParaIngreso($ingreso->id, session('usuario_id')));

        $mecanicos = Usuario::where('rol_id', 3)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $ordenesPendientes = Orden::with('vehiculo')
            ->whereNull('mecanico_id')
            ->whereNotIn('estado', [
                'finalizada',
                'entregada',
                'cancelada'
            ])
            ->orderByDesc('updated_at')
            ->get();

        $asignacionesActivas = Orden::with('vehiculo')
            ->whereNotNull('mecanico_id')
            ->orderByDesc('updated_at')
            ->get();

        return view('livewire.asignaciones', compact(
            'mecanicos',
            'ordenesPendientes',
            'asignacionesActivas'
        ))->layout('layouts.app');
    }

    public function nuevaAsignacion()
    {
        $this->reset(['orden_id', 'mecanico_id']);
        $this->resetValidation();
        $this->mostrar_formulario = true;
    }

    public function cancelar()
    {
        $this->mostrar_formulario = false;
        $this->reset(['orden_id', 'mecanico_id']);
    }

    public function guardar()
    {
        if (!in_array(session('usuario_rol'), [
            'Administrador',
            'Jefe de taller',
            'Jefe de Taller'
        ])) {
            abort(403);
        }

        $datos = $this->validate([
            'orden_id' => 'required|integer|exists:ordenes,id',
            'mecanico_id' => 'required|integer|exists:usuarios,id',
        ]);

        $mecanico = Usuario::where('id', $datos['mecanico_id'])
            ->where('rol_id', 3)
            ->where('activo', true)
            ->first();

        if (!$mecanico) {
            $this->addError(
                'mecanico_id',
                'Selecciona un mecánico activo.'
            );
            return;
        }

        $orden = Orden::findOrFail($datos['orden_id']);

        if ($orden->mecanico_id !== null) {
            $this->addError(
                'orden_id',
                'Esta orden ya está asignada a un mecánico.'
            );
            return;
        }

        $orden->mecanico_id = $mecanico->id;
        $orden->save();

        $this->mostrar_formulario = false;
        $this->reset(['orden_id', 'mecanico_id']);

        session()->flash(
            'mensaje',
            'Orden asignada correctamente.'
        );
    }
}

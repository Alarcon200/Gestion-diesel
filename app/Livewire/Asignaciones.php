<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orden;    // Asegúrate de que este modelo exista
use App\Models\Usuario;  // Modelo de usuarios que ya arreglamos

class Asignaciones extends Component
{
    public $orden_id, $mecanico_id;
    public $mostrar_formulario = false;

    public function mount()
    {
        // Protegemos para que solo entre el Jefe de Taller y el Administrador
        if (!session('usuario_id') || !in_array(session('usuario_rol'), ['Administrador', 'Jefe de Taller'])) {
            return redirect('/panel');
        }
    }

    public function render()
    {
        // Traemos a todos los usuarios que son Mecánicos (asumiendo que el ID del rol es 4)
        $mecanicos = Usuario::where('rol_id', 4)->get();
        
        // Traemos las órdenes que aún NO tienen un mecánico asignado
        $ordenesPendientes = Orden::whereNull('mecanico_id')->get();
        
        // Traemos las órdenes que YA están asignadas para verlas en la tabla
        $asignacionesActivas = Orden::whereNotNull('mecanico_id')
                                    ->with('vehiculo', 'mecanico') // Si tienes relación con el vehículo y el mecánico
                                    ->orderBy('updated_at', 'desc')
                                    ->get();

        return view('livewire.asignaciones', compact('mecanicos', 'ordenesPendientes', 'asignacionesActivas'))
            ->layout('layouts.app');
    }

    public function nuevaAsignacion()
    {
        $this->orden_id = '';
        $this->mecanico_id = '';
        $this->mostrar_formulario = true;
    }

    public function cancelar()
    {
        $this->mostrar_formulario = false;
    }

    public function guardar()
    {
        $this->validate([
            'orden_id' => 'required',
            'mecanico_id' => 'required'
        ], [
            'orden_id.required' => 'Debes seleccionar un vehículo/orden.',
            'mecanico_id.required' => 'Debes asignar un mecánico.'
        ]);

        // Buscamos la orden y le asignamos el mecánico
        $orden = Orden::find($this->orden_id);
        $orden->mecanico_id = $this->mecanico_id;
        // Opcional: si manejas estados, puedes pasarlo a 'En proceso'
        // $orden->estado = 'En proceso'; 
        $orden->save();

        $this->mostrar_formulario = false;
        
        session()->flash('mensaje', '¡Trabajo asignado correctamente al mecánico!');
    }
}
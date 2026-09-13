<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Orden;
use App\Models\Ingreso;

class Ordenes extends Component
{
    public $ingreso_id = '';
    public $mostrar_formulario = false;

    protected $rules = [
        'ingreso_id' => 'required|exists:ingresos,id',
    ];

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function nuevo()
    {
        $this->reset(['ingreso_id']);
        $this->resetValidation();
        $this->mostrar_formulario = true;
    }

    public function guardar()
    {
        $this->validate();

        $ingreso = Ingreso::findOrFail($this->ingreso_id);

        Orden::create([
            'numero_orden' => 'OT-' . str_pad((Orden::max('id') + 1), 5, '0', STR_PAD_LEFT),
            'ingreso_id' => $ingreso->id,
            'vehiculo_id' => $ingreso->vehiculo_id,
            'usuario_id' => session('usuario_id'),
            'fecha_creacion' => now(),
            'estado' => 'pendiente',
        ]);

        $this->reset(['ingreso_id']);
        $this->mostrar_formulario = false;
    }

    public function cancelar()
    {
        $this->reset(['ingreso_id']);
        $this->mostrar_formulario = false;
    }

    public function render()
    {
        return view('livewire.ordenes', [
            'ordenes' => Orden::with('vehiculo.cliente', 'usuario')->orderByDesc('fecha_creacion')->get(),
            'ingresos' => Ingreso::with('vehiculo.cliente')->whereDoesntHave('ordenes')->orderByDesc('fecha_ingreso')->get(),
        ])->layout('layouts.app');
    }
}
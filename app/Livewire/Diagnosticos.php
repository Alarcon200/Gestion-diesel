<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diagnostico;
use App\Models\Ingreso;
use App\Models\Orden;

class Diagnosticos extends Component
{
    public $ingreso_id = '';
    public $problema_encontrado = '';
    public $causa_probable = '';
    public $trabajo_a_realizar = '';
    public $recomendaciones = '';
    public $mostrar_formulario = false;

    protected $rules = [
        'ingreso_id' => 'required|exists:ingresos,id',
        'problema_encontrado' => 'required|string',
        'trabajo_a_realizar' => 'required|string',
    ];

    public function mount()
    {
        if (!session('usuario_id')) {
            return redirect('/login');
        }
    }

    public function nuevo()
    {
        $this->resetForm();
        $this->mostrar_formulario = true;
    }

    public function guardar()
    {
        $this->validate();

        $orden = Orden::obtenerOCrearParaIngreso($this->ingreso_id, session('usuario_id'));

        Diagnostico::create([
            'orden_id' => $orden->id,
            'usuario_id' => session('usuario_id'),
            'problema_encontrado' => $this->problema_encontrado,
            'causa_probable' => $this->causa_probable,
            'trabajo_a_realizar' => $this->trabajo_a_realizar,
            'recomendaciones' => $this->recomendaciones,
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

    private function resetForm()
    {
        $this->reset(['ingreso_id', 'problema_encontrado', 'causa_probable', 'trabajo_a_realizar', 'recomendaciones']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.diagnosticos', [
            'diagnosticos' => Diagnostico::with('orden.vehiculo.cliente', 'usuario')->orderByDesc('fecha')->get(),
            'ingresos' => Ingreso::with('vehiculo')->orderByDesc('fecha_ingreso')->get(),
        ])->layout('layouts.app');
    }
}
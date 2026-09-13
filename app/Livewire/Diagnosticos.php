<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diagnostico;
use App\Models\Orden;

class Diagnosticos extends Component
{
    public $orden_id = '';
    public $problema_encontrado = '';
    public $causa_probable = '';
    public $trabajo_a_realizar = '';
    public $recomendaciones = '';
    public $mostrar_formulario = false;

    protected $rules = [
        'orden_id' => 'required|exists:ordenes,id',
        'problema_encontrado' => 'required|string',
        'causa_probable' => 'nullable|string',
        'trabajo_a_realizar' => 'required|string',
        'recomendaciones' => 'nullable|string',
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

        Diagnostico::create([
            'orden_id' => $this->orden_id,
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
        $this->reset(['orden_id', 'problema_encontrado', 'causa_probable', 'trabajo_a_realizar', 'recomendaciones']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.diagnosticos', [
            'diagnosticos' => Diagnostico::with('orden.vehiculo.cliente', 'usuario')->orderByDesc('fecha')->get(),
            'ordenes' => Orden::with('vehiculo')->orderByDesc('fecha_creacion')->get(),
        ])->layout('layouts.app');
    }
}
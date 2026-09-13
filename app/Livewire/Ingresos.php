<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ingreso;
use App\Models\Vehiculo;

class Ingresos extends Component
{
    public $vehiculo_id = '';
    public $conductor = '';
    public $fecha_ingreso = '';
    public $motivo = '';
    public $kilometraje = '';
    public $mostrar_formulario = false;

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

        Ingreso::create([
            'vehiculo_id' => $this->vehiculo_id,
            'usuario_id' => session('usuario_id'),
            'conductor' => $this->conductor,
            'fecha_ingreso' => $this->fecha_ingreso,
            'motivo' => $this->motivo,
            'kilometraje' => $this->kilometraje ?: null,
            'estado' => 'en_taller',
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
        $this->reset(['vehiculo_id', 'conductor', 'fecha_ingreso', 'motivo', 'kilometraje']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.ingresos', [
            'ingresos' => Ingreso::with('vehiculo.cliente', 'usuario')->orderByDesc('fecha_ingreso')->get(),
            'vehiculos' => Vehiculo::with('cliente')->orderBy('placa')->get(),
        ])->layout('layouts.app');
    }
}